<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Services\ChatGptService;
use App\Traits\AIArticleable;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateDocument extends CreateRecord
{
    use AIArticleable;

    protected static string $resource = DocumentResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string | Htmlable
    {
        return __('Creat New Document');
    }

    // protected static string $view = 'filament.resources.document-resource.pages.create-custom-document';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // $data['bullets'] = str_replace('\\n', '<br>', $data['bullets']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Customize the creation process
        return static::getModel()::create($data);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Document saved')
            ->body('Document has been created successfully.');
    }

    /**
     * before a form is saved https://filamentphp.com/docs/3.x/panels/resources/creating-records#lifecycle-hooks
     */
    protected function beforeCreate(): void
    {
        // ...
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getSaveAsDraftFormAction(),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }


    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
        ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
        ->submit('create')
        ->keyBindings(['mod+s']);
    }

    protected function getSubmitFormAction(): Action
    {
        return $this->getCreateFormAction();
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
        ->label(__('filament-panels::resources/pages/create-record.form.actions.create_another.label'))
        ->action('createAnother')
        ->keyBindings(['mod+shift+s'])
        ->color('gray');
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
        ->label(__('filament-panels::resources/pages/create-record.form.actions.cancel.label'))
        ->url($this->previousUrl ?? static::getResource()::getUrl())
            ->color('gray');
    }

    protected function getSaveAsDraftFormAction(): Action
    {
        return Action::make('saveAsDraft')
        ->label(__('filament-panels::resources/pages/create-record.form.actions.save_as_draft.label'))
        ->submit('saveAsDraft');
    }

    public function generatePlot(array $data)
    {
        $summary = $this->generateArticle($data, 'summary');
        $bullets = $this->generateArticle($data, 'bullets');

        return [
            'summary' => $summary,
            'bullets' => $bullets,
        ];
    }

    public function generateResult(array $data)
    {
        $result = $this->generateArticle($data, 'result');

        return [
            'result' => $result
        ];
    }

}
