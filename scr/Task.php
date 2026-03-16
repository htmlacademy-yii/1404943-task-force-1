<?php

namespace TaskForce;

class Task
{
    public const string STATUS_NEW = 'status_new';  //  Задание опубликовано, исполнитель ещё не найден
    public const string STATUS_CANCEL = 'status_cancel'; // Заказчик отменил задание
    public const string STATUS_IN_PROGRESS = 'status_in_progress'; // Заказчик выбрал исполнителя для задания
    public const string STATUS_FAILED = 'status_failed'; // Исполнитель отказался от выполнения задания
    public const string STATUS_DONE = 'status_done';  // Заказчик отметил задание как выполненное
    public const string ACTION_CANCEL = 'action_cancel';  //  Действие при отмене задания заказчиком
    public const string ACTION_RESPOND = 'action_respond'; // Действие при отклике на задание исполнителем
    public const string ACTION_START = 'action_start'; // Действие при начале выполнения задания
    public const string ACTION_REJECT = 'action_reject'; // Действие при отмене задания исполнителем
    public const string ACTION_DONE = 'action_done'; // Действие принятия задания
    private int $customerID;
    private ?int $executorID;
    private string $currentStatus;

    public function __construct(int $customerID, ?int $executorID = null)
    {
        $this->customerID = $customerID;
        $this->executorID = $executorID;
        $this->currentStatus = self::STATUS_NEW;
    }

    public function getStatus(): string
    {
        return $this->currentStatus;
    }

    public function getStatusesMap(): array
    {
        return [
            self::STATUS_NEW => 'Новое',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_FAILED => 'Провалено',
            self::STATUS_DONE => 'Выполнено',
            self::STATUS_CANCEL => "Отменено",
        ];
    }

    public function getActionsMap(): array
    {
        return [
            self::ACTION_CANCEL => 'Отменить',
            self::ACTION_DONE => 'Завершить задание',
            self::ACTION_RESPOND => 'Откликнуться на задание',
            self::ACTION_REJECT => 'Отказаться от задания',
            self::ACTION_START => 'Начать выполнение',
        ];
    }

    public function getNextStatus(string $action): ?string
    {
        return match ($action) {
            self::ACTION_CANCEL => self::STATUS_CANCEL,
            self::ACTION_DONE => self::STATUS_DONE,
            self::ACTION_REJECT => self::STATUS_FAILED,
            self::ACTION_START => self::STATUS_IN_PROGRESS,
            default => null,
        };
    }

    public function getAvailableActions(string $status): array
    {
        return match ($status) {
            self::STATUS_NEW => [self::ACTION_CANCEL, self::ACTION_RESPOND, self::ACTION_START],
            self::STATUS_IN_PROGRESS => [self::ACTION_DONE, self::ACTION_REJECT],
            default => [],
        };
    }

    public function applyAction(string $action): bool
    {

        $availableActions = $this->getAvailableActions($this->currentStatus);
        if (!in_array($action, $availableActions)) {
            return false;
        }

        $newStatus = $this->getNextStatus($action);

        if ($newStatus === null && $action !== self::ACTION_RESPOND) {
            return false;
        }

        if ($newStatus !== null) {
            $this->currentStatus = $newStatus;
        }

        return true;
    }

    public function getCustomerID(): int
    {
        return $this->customerID;
    }

    public function getExecutorID(): ?int
    {
        return $this->executorID;
    }
}
