<div id="editOrderModal" tabindex="-1" aria-hidden="true"
     class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50
            flex justify-center items-center">

    <form id="edit-order-form" method="POST" class="relative p-4 w-full max-w-xl max-h-full">
        @csrf
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl dark:shadow-gray-900/50 overflow-hidden">

            <!-- Header -->
            <div class="relative bg-gradient-to-br from-indigo-600 to-indigo-500 px-6 py-4 overflow-hidden">
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-white/5 rounded-full pointer-events-none"></div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="bi bi-pencil-fill text-white text-sm"></i>
                        </div>
                        <h5 id="editOrderModalLabel" class="text-white font-semibold text-base"></h5>
                    </div>
                    <button type="button" id="close-order-modal"
                            class="w-8 h-8 flex items-center justify-center rounded-lg
                                   text-white/70 hover:text-white hover:bg-white/20
                                   transition-colors">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4">

                <div id="div-alert-order"></div>

                <!-- Plats -->
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest
                               text-gray-400 dark:text-gray-500 mb-2.5">
                        Quantités
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($dishes as $dish)
                            <div class="flex items-center justify-between gap-3
                                        bg-gray-50 dark:bg-gray-700/50
                                        border border-gray-200 dark:border-gray-700
                                        rounded-xl px-3.5 py-2.5">
                                <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                    <i class="bi bi-egg-fried text-indigo-400 dark:text-indigo-500 flex-shrink-0"></i>
                                    <label for="dish-{{ $dish->id }}"
                                           class="text-sm text-gray-700 dark:text-gray-300 truncate cursor-pointer">
                                        {{ $dish->name }}
                                    </label>
                                </div>
                                <input type="number"
                                       id="dish-{{ $dish->id }}"
                                       name="dishes[{{ $dish->id }}]"
                                       class="w-16 text-center font-bold
                                              bg-white dark:bg-gray-700
                                              border-2 border-gray-200 dark:border-gray-600
                                              text-gray-900 dark:text-gray-100
                                              text-sm rounded-lg flex-shrink-0
                                              focus:outline-none focus:border-indigo-500
                                              py-1.5 transition-colors"
                                       value="0" min="0">
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Personnalisation -->
                <div>
                    <label for="perso"
                           class="block text-[11px] font-bold uppercase tracking-widest
                                  text-gray-400 dark:text-gray-500 mb-2.5">
                        Commentaires
                        <span class="font-normal normal-case tracking-normal"> — optionnel</span>
                    </label>
                    <textarea name="perso" id="perso" rows="2"
                              placeholder="Ex : sans sauce, bien cuit…"
                              class="w-full bg-gray-50 dark:bg-gray-700
                                     border-2 border-gray-200 dark:border-gray-600
                                     text-gray-900 dark:text-gray-100
                                     placeholder-gray-400 dark:placeholder-gray-500
                                     text-sm rounded-xl resize-none p-3 transition-colors
                                     focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 px-5 py-4
                        border-t border-gray-100 dark:border-gray-700
                        bg-gray-50/50 dark:bg-gray-700/20">
                <button type="button" id="cancel-order-modal"
                        class="px-4 py-2.5 text-sm font-medium rounded-xl transition-colors
                               text-gray-700 dark:text-gray-300
                               bg-white dark:bg-gray-700
                               border border-gray-200 dark:border-gray-600
                               hover:bg-gray-50 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button type="submit"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl
                               text-white bg-indigo-600 hover:bg-indigo-700 transition-colors
                               shadow-md shadow-indigo-500/25">
                    <i class="bi bi-check2"></i>
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
</div>
