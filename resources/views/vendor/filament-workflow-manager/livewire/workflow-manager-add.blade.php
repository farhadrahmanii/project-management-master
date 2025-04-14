<form wire:submit.prevent="submit" class="w-full flex flex-col justify-start items-start">
    <div class="w-full mb-5 p-5">
        {{ $this->form }}
    </div>

    <div
        class="w-full flex flex-row justify-start items-center gap-2 border-t border-gray-200 dark:border-gray-700 px-5">
        <button type="submit"
            class="mt-5 px-3 py-1 text-white dark:text-gray-900 bg-primary-600 hover:text-primary-600 dark:hover:text-primary-600 hover:bg-white dark:hover:bg-gray-100 border border-primary-600 rounded transition-colors">
            {{ __('filament-workflow-manager::filament-workflow-manager.resources.workflow.page.workflow.modal.add.form.submit') }}
        </button>
        <button type="button" wire:click="cancel"
            class="mt-5 px-3 py-1 text-white dark:text-gray-900 bg-danger-600 hover:text-danger-600 dark:hover:text-danger-600 hover:bg-white dark:hover:bg-gray-100 border border-danger-600 rounded transition-colors">
            {{ __('filament-workflow-manager::filament-workflow-manager.resources.workflow.page.workflow.modal.add.form.cancel') }}
        </button>
        <div class="w-8 h-8 mt-5" wire:loading>
            <x-filament-support::loading-indicator
                class="inline-block animate-spin w-8 h-8 mr-3 text-primary-600 dark:text-primary-400" />
        </div>
    </div>
</form>