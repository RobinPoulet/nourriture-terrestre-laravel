<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\DeviceAuth;
use App\Services\MenuService;
use App\Support\MenuCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly MenuService $menus, private readonly DeviceAuth $device) {}

    /**
     * Commandes du jour et récapitulatif
     */
    public function index(): View
    {
        $menu = $this->menus->current();
        $today = now()->toDateString();

        // Deadline de fermeture du formulaire (lundi 11h15, null si ouverture forcée ou formulaire fermé)
        $naturallyOpen = MenuCalendar::canDisplayOrderForm($menu->creation_date);

        return view('orders.index', [
            'dishes' => $menu->dishes,
            'orders' => Order::query()->with(['user', 'dishes'])->where('creation_date', $today)->get(),
            'isOpen' => $menu->is_open,
            'tabTotalQuantity' => Order::totalQuantityByDish($today),
            'smsSent' => $menu->is_open ? $menu->smsResponse?->summary() : null,
            'selectedUserId' => $this->device->user()?->id,
            'canDisplayForm' => $naturallyOpen || Setting::isEnabled(SettingKey::ForceOpenForm),
            'formDeadlineTs' => $naturallyOpen ? MenuCalendar::orderDeadlineTimestamp() : null,
        ]);
    }

    /**
     * Formulaire de prise de commande
     */
    public function create(): View
    {
        $menu = $this->menus->current();

        return view('orders.create', [
            'users' => User::query()->orderBy('name')->get(),
            'dishes' => $menu->dishes,
            'isOpen' => $menu->is_open,
            'dateMenu' => $menu->creation_date,
            'canDisplayForm' => MenuCalendar::canDisplayOrderForm($menu->creation_date)
                || Setting::isEnabled(SettingKey::ForceOpenForm),
            'selectedUserId' => $this->device->user()?->id,
        ]);
    }

    /**
     * Enregistrer une nouvelle commande
     */
    public function store(Request $request): RedirectResponse
    {
        $dishes = $this->dishQuantities($request);
        $userId = $request->input('user');

        $errors = [];
        if (! $this->hasAtLeastOneDish($dishes)) {
            $errors[] = 'Il faut commander au moins un plat';
        }
        if ($userId === null) {
            $errors[] = 'Merci de sélectionner un nom';
        }
        if (! empty($errors)) {
            return redirect()->route('orders.create')->withErrors($errors);
        }

        $user = User::query()->find((int) $userId);
        $deviceUser = $this->device->user();
        $isSameUser = $deviceUser !== null && $user !== null && (int) $deviceUser->id === (int) $user->id;

        $error = match (true) {
            $user === null => 'Utilisateur introuvable',
            // Cet appareil est déjà associé à quelqu'un d'autre : on ne laisse pas "réclamer" un autre compte
            $deviceUser !== null && ! $isSameUser => "Cet appareil est déjà associé à $deviceUser->name",
            // L'utilisateur est déjà identifié sur une autre machine
            $user->cookie_hash !== null && ! $isSameUser => 'Utilisateur déjà connecté sur une autre machine',
            default => null,
        };
        if ($error !== null) {
            return redirect()->route('orders.create')->withErrors([$error]);
        }

        DB::transaction(function () use ($user, $request, $dishes) {
            $order = $user->orders()->create(['perso' => (string) $request->input('perso', '')]);
            $order->dishes()->attach($this->pivotData($dishes));
        });

        // Premier enregistrement → nouveau jeton ; sinon on prolonge le cookie existant
        if ($isSameUser) {
            $this->device->refresh();
        } else {
            $this->device->remember($user);
        }

        return redirect()->route('orders.index')->with('success', "Ta commande a bien été enregistrée $user->name");
    }

    /**
     * Modifier sa commande
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        if (! $this->ownsOrder($order)) {
            return redirect()->route('orders.index')->withErrors(['Tu ne peux modifier que ta propre commande']);
        }

        $dishes = $this->dishQuantities($request);
        if (! $this->hasAtLeastOneDish($dishes)) {
            return redirect()->route('orders.index')->withErrors(['Il faut commander au moins un plat']);
        }

        DB::transaction(function () use ($order, $request, $dishes) {
            $order->dishes()->sync($this->pivotData($dishes));
            $order->update(['perso' => (string) $request->input('perso', '')]);
        });

        return redirect()->route('orders.index')->with('success', "Ta commande a bien été modifiée {$order->user->name}");
    }

    /**
     * Supprimer sa commande
     */
    public function destroy(Order $order): RedirectResponse
    {
        if (! $this->ownsOrder($order)) {
            return redirect()->route('orders.index')->withErrors(['Tu ne peux supprimer que ta propre commande']);
        }

        $userName = $order->user?->name;
        DB::transaction(function () use ($order) {
            $order->dishes()->detach();
            $order->delete();
        });

        return redirect()->route('orders.index')->with('success', "Ta commande a bien été supprimée $userName");
    }

    /**
     * La commande appartient-elle à l'utilisateur identifié sur cet appareil ?
     */
    private function ownsOrder(Order $order): bool
    {
        $deviceUserId = $this->device->user()?->id;

        return $deviceUserId !== null && (int) $deviceUserId === (int) $order->user_id;
    }

    /**
     * Quantités saisies, sous forme [dish_id => quantité]
     *
     * @return array<int, mixed>
     */
    private function dishQuantities(Request $request): array
    {
        $dishes = $request->input('dishes', []);

        return is_array($dishes) ? $dishes : [];
    }

    /**
     * Au moins un plat avec une quantité entière strictement positive
     */
    private function hasAtLeastOneDish(array $dishes): bool
    {
        foreach ($dishes as $quantity) {
            if (is_scalar($quantity) && ctype_digit((string) $quantity) && (int) $quantity > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Données de la table pivot order_dishes : [dish_id => ['quantity' => n]]
     *
     * @return array<int, array{quantity: int}>
     */
    private function pivotData(array $dishes): array
    {
        $pivot = [];
        foreach ($dishes as $dishId => $quantity) {
            $pivot[(int) $dishId] = ['quantity' => is_scalar($quantity) ? max(0, (int) $quantity) : 0];
        }

        return $pivot;
    }
}
