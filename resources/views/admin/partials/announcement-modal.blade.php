<!-- Modale Ajouter / Éditer une annonce -->
<div id="announcementModal" tabindex="-1" aria-hidden="true"
     class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50
            flex justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl dark:shadow-gray-900/50">
            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                <h5 class="text-base font-semibold text-gray-900 dark:text-white" id="announcementModalLabel">
                    Ajouter une annonce
                </h5>
                <button type="button" id="close-announcement-modal"
                        class="text-gray-400 dark:text-gray-500
                               hover:bg-gray-100 dark:hover:bg-gray-700
                               hover:text-gray-900 dark:hover:text-white
                               rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <!-- Body -->
            <form action="{{ route('admin.announcements.store') }}" method="post" id="announcement-form">
                @csrf
                <div class="p-5 space-y-4">
                    <div id="alert-announcement-modal"></div>
                    <div>
                        <label for="announcement-message"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Message
                        </label>
                        <textarea id="announcement-message" name="message" rows="3"
                                  placeholder="Texte affiché dans le ticker…"
                                  class="bg-gray-50 dark:bg-gray-700
                                         border border-gray-300 dark:border-gray-600
                                         text-gray-900 dark:text-gray-100
                                         placeholder-gray-400 dark:placeholder-gray-500
                                         text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500
                                         block w-full p-2.5 resize-y"></textarea>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" id="cancel-announcement-modal"
                            class="text-sm font-medium text-gray-700 dark:text-gray-300
                                   bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600
                                   rounded-lg px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" id="announcement-validate"
                            class="text-sm font-medium text-white bg-indigo-600 rounded-lg px-4 py-2
                                   hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-500/20">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
