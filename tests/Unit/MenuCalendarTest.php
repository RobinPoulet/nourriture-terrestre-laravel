<?php

namespace Tests\Unit;

use App\Support\MenuCalendar;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * 2026-10-04 est un dimanche (publication du menu), 2026-10-05 le lundi suivant
 */
class MenuCalendarTest extends TestCase
{
    private const string MENU_DATE = '2026-10-04';

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public static function orderFormProvider(): array
    {
        return [
            'lundi matin' => ['2026-10-05 08:00', self::MENU_DATE, true],
            'lundi 11h15 pile' => ['2026-10-05 11:15', self::MENU_DATE, true],
            'lundi 11h16' => ['2026-10-05 11:16', self::MENU_DATE, false],
            'mardi' => ['2026-10-06 09:00', self::MENU_DATE, false],
            'dimanche' => ['2026-10-04 20:00', self::MENU_DATE, false],
            'pas de menu cette semaine' => ['2026-10-05 09:00', '2026-09-27', false],
        ];
    }

    #[DataProvider('orderFormProvider')]
    public function test_order_form_window(string $now, string $menuDate, bool $expected): void
    {
        Carbon::setTestNow($now);

        $this->assertSame($expected, MenuCalendar::canDisplayOrderForm($menuDate));
    }

    public static function voteProvider(): array
    {
        return [
            'lundi 12h44' => ['2026-10-05 12:44', self::MENU_DATE, false],
            'lundi 12h45' => ['2026-10-05 12:45', self::MENU_DATE, true],
            'samedi' => ['2026-10-10 18:00', self::MENU_DATE, true],
            'dimanche suivant' => ['2026-10-11 10:00', self::MENU_DATE, false],
            'menu de plus d\'une semaine' => ['2026-10-13 10:00', self::MENU_DATE, false],
        ];
    }

    #[DataProvider('voteProvider')]
    public function test_vote_window(string $now, string $menuDate, bool $expected): void
    {
        Carbon::setTestNow($now);

        $this->assertSame($expected, MenuCalendar::canVote($menuDate));
    }

    public function test_monday_date_of_current_week(): void
    {
        Carbon::setTestNow('2026-10-11 10:00'); // dimanche

        $this->assertSame('2026-10-05', MenuCalendar::mondayDate());
    }

    public function test_menu_date_is_formatted_as_following_monday(): void
    {
        $this->assertSame('lundi 5 octobre 2026', MenuCalendar::formatMenuMonday(self::MENU_DATE));
    }

    public function test_order_deadline_is_today_at_11_15(): void
    {
        Carbon::setTestNow('2026-10-05 08:00');

        $this->assertSame(Carbon::parse('2026-10-05 11:15')->getTimestamp(), MenuCalendar::orderDeadlineTimestamp());
    }
}
