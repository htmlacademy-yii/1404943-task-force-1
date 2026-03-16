<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TaskForce\Task;

class TaskTest extends TestCase
{
    private int $customerId = 1;
    private ?int $executorId = 2;

    public function testConstructorSetsCorrectInitialValues(): void
    {
        $task = new Task($this->customerId, $this->executorId);

        $this->assertEquals($this->customerId, $task->getCustomerID());
        $this->assertEquals($this->executorId, $task->getExecutorID());
        $this->assertEquals(Task::STATUS_NEW, $task->getStatus());
    }

    public function testConstructorSetsExecutorIdToNullWhenNotProvided(): void
    {
        $task = new Task($this->customerId);

        $this->assertNull($task->getExecutorID());
    }

    public function testGetStatusesMapReturnsArrayWithAllStatuses(): void
    {
        $task = new Task($this->customerId);
        $statusesMap = $task->getStatusesMap();

        $expectedStatuses = [
            Task::STATUS_NEW => 'Новое',
            Task::STATUS_IN_PROGRESS => 'В работе',
            Task::STATUS_FAILED => 'Провалено',
            Task::STATUS_DONE => 'Выполнено',
            Task::STATUS_CANCEL => 'Отменено',
        ];

        $this->assertEquals($expectedStatuses, $statusesMap);
        $this->assertCount(5, $statusesMap);
    }

    public function testGetActionsMapReturnsArrayWithAllActions(): void
    {
        $task = new Task($this->customerId);
        $actionsMap = $task->getActionsMap();

        $expectedActions = [
            Task::ACTION_CANCEL => 'Отменить',
            Task::ACTION_DONE => 'Завершить задание',
            Task::ACTION_RESPOND => 'Откликнуться на задание',
            Task::ACTION_REJECT => 'Отказаться от задания',
            Task::ACTION_START => 'Начать выполнение',
        ];

        $this->assertEquals($expectedActions, $actionsMap);
        $this->assertCount(5, $actionsMap);
    }

    #[DataProvider('actionToStatusProvider')]
    public function testGetNextStatusReturnsCorrectStatusForAction(string $action, ?string $expectedStatus): void
    {
        $task = new Task($this->customerId);

        $this->assertEquals($expectedStatus, $task->getNextStatus($action));
    }

    public static function actionToStatusProvider(): array
    {
        return [
            'cancel action leads to cancel status' => [Task::ACTION_CANCEL, Task::STATUS_CANCEL],
            'done action leads to done status' => [Task::ACTION_DONE, Task::STATUS_DONE],
            'reject action leads to failed status' => [Task::ACTION_REJECT, Task::STATUS_FAILED],
            'start action leads to in progress status' => [Task::ACTION_START, Task::STATUS_IN_PROGRESS],
            'unknown action returns null' => ['unknown_action', null],
            'respond action returns null' => [Task::ACTION_RESPOND, null],
        ];
    }

    #[DataProvider('statusToAvailableActionsProvider')]
    public function testGetAvailableActionsReturnsCorrectActionsForStatus(string $status, array $expectedActions): void
    {
        $task = new Task($this->customerId);

        $this->assertEquals($expectedActions, $task->getAvailableActions($status));
    }

    public static function statusToAvailableActionsProvider(): array
    {
        return [
            'new status allows cancel, respond, start' => [
                Task::STATUS_NEW,
                [Task::ACTION_CANCEL, Task::ACTION_RESPOND, Task::ACTION_START]
            ],
            'in progress allows done, reject' => [
                Task::STATUS_IN_PROGRESS,
                [Task::ACTION_DONE, Task::ACTION_REJECT]
            ],
            'failed status allows no actions' => [Task::STATUS_FAILED, []],
            'done status allows no actions' => [Task::STATUS_DONE, []],
            'cancel status allows no actions' => [Task::STATUS_CANCEL, []],
        ];
    }

    #[DataProvider('successfulApplyActionProvider')]
    public function testApplyActionSuccessfullyChangesStatus(string $initialStatus, string $action, string $expectedStatus): void
    {
        $task = $this->createTaskWithReflection($initialStatus);

        $result = $task->applyAction($action);

        $this->assertTrue($result);
        $this->assertEquals($expectedStatus, $task->getStatus());
    }

    public static function successfulApplyActionProvider(): array
    {
        return [
            'cancel action on new status' => [Task::STATUS_NEW, Task::ACTION_CANCEL, Task::STATUS_CANCEL],
            'start action on new status' => [Task::STATUS_NEW, Task::ACTION_START, Task::STATUS_IN_PROGRESS],
            'done action on in progress' => [Task::STATUS_IN_PROGRESS, Task::ACTION_DONE, Task::STATUS_DONE],
            'reject action on in progress' => [Task::STATUS_IN_PROGRESS, Task::ACTION_REJECT, Task::STATUS_FAILED],
        ];
    }

    public function testApplyActionWithRespondActionDoesNotChangeStatus(): void
    {
        $task = $this->createTaskWithReflection(Task::STATUS_NEW);

        $result = $task->applyAction(Task::ACTION_RESPOND);

        $this->assertTrue($result);
        $this->assertEquals(Task::STATUS_NEW, $task->getStatus());
    }

    #[DataProvider('unsuccessfulApplyActionProvider')]
    public function testApplyActionReturnsFalseForInvalidAction(string $initialStatus, string $action): void
    {
        $task = $this->createTaskWithReflection($initialStatus);

        $result = $task->applyAction($action);

        $this->assertFalse($result);
        $this->assertEquals($initialStatus, $task->getStatus());
    }

    public static function unsuccessfulApplyActionProvider(): array
    {
        return [
            'done action on new status' => [Task::STATUS_NEW, Task::ACTION_DONE],
            'reject action on new status' => [Task::STATUS_NEW, Task::ACTION_REJECT],
            'cancel action on in progress' => [Task::STATUS_IN_PROGRESS, Task::ACTION_CANCEL],
            'start action on in progress' => [Task::STATUS_IN_PROGRESS, Task::ACTION_START],
            'respond action on in progress' => [Task::STATUS_IN_PROGRESS, Task::ACTION_RESPOND],
            'unknown action on new status' => [Task::STATUS_NEW, 'unknown_action'],
            'unknown action on in progress' => [Task::STATUS_IN_PROGRESS, 'unknown_action'],
            'action on failed status' => [Task::STATUS_FAILED, Task::ACTION_CANCEL],
            'action on done status' => [Task::STATUS_DONE, Task::ACTION_DONE],
            'action on cancel status' => [Task::STATUS_CANCEL, Task::ACTION_START],
        ];
    }

    public function testApplyActionMaintainsStatusWhenActionNotInAvailableActions(): void
    {
        $task = $this->createTaskWithReflection(Task::STATUS_NEW);

        $result = $task->applyAction(Task::ACTION_DONE);

        $this->assertFalse($result);
        $this->assertEquals(Task::STATUS_NEW, $task->getStatus());
    }

    public function testGetCustomerIDReturnsCorrectId(): void
    {
        $task = new Task($this->customerId);

        $this->assertEquals($this->customerId, $task->getCustomerID());
    }

    public function testGetExecutorIDReturnsCorrectId(): void
    {
        $task = new Task($this->customerId, $this->executorId);

        $this->assertEquals($this->executorId, $task->getExecutorID());
    }

    public function testGetExecutorIDReturnsNullWhenNoExecutor(): void
    {
        $task = new Task($this->customerId);

        $this->assertNull($task->getExecutorID());
    }

    private function createTaskWithReflection(string $status): Task
    {
        $task = new Task($this->customerId, $this->executorId);

        $reflection = new ReflectionClass($task);
        $property = $reflection->getProperty('currentStatus');
        $property->setValue($task, $status);

        return $task;
    }
}
