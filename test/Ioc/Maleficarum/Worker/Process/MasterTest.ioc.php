<?php

\Maleficarum\Ioc\Container::register('Maleficarum\Worker\Process\Master', function () {
    $profilerMock = $this->createMock('Maleficarum\Profiler\Time\Generic');
    $profilerMock->expects($this->any())->method('clear')->will($this->returnValue($profilerMock));
    $profilerMock->expects($this->any())->method('begin')->will($this->returnValue($profilerMock));
    $profilerMock->expects($this->any())->method('end')->will($this->returnValue($profilerMock));

    $configMock = $this->createMock('Maleficarum\Config\Ini\Config');
    $loggerMock = $this->createMock('Maleficarum\Worker\Logger\Logger');
    $queueMock = $this->createMock('Maleficarum\Rabbitmq\Connection\Connection');
    $channelMock = $this->createMock('PhpAmqpLib\Channel\AMQPChannel');

    // testMainLoop
    if ($this->getContext() === 'testMainLoop') {
        $configMock
            ->expects($this->once())
            ->method('offsetGet')
            ->with($this->equalTo('queue'))
            ->will($this->returnValue(['commands' => ['queue-name' => 'test-queue']]));

        $queueMock
            ->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($channelMock));

        $channelMock
            ->expects($this->once())
            ->method('basic_consume')
            ->with($this->equalTo('test-queue'));
    }

    return (new \Maleficarum\Worker\Process\Master())
        ->addProfiler($profilerMock, 'time')
        ->setConfig($configMock)
        ->setLogger($loggerMock)
        ->setQueue($queueMock);
});

\Maleficarum\Ioc\Container::register('PhpAmqpLib\Message\AMQPMessage', function () {
    $msg = $this->createMock('PhpAmqpLib\Message\AMQPMessage');

    $msg->delivery_info['delivery_tag'] = 'test_tag';
    $msg->delivery_info['channel'] = $this->createMock('PhpAmqpLib\Channel\AMQPChannel');

    // testCommandHandlingWithNonExistentCommandDefinition
    if ($this->getContext() === 'testCommandHandlingWithNonExistentCommandDefinition') {
        $msg->body = '{"__type":"' . uniqid() . '"}';
    }

    // testCommandHandlingWithCorrectCommand
    if ($this->getContext() === 'testCommandHandlingWithCorrectCommand') {
        $msg->body = '{"__type":"Log\\\\Generic","msg":"test"}';
    }

    return $msg;
});

\Maleficarum\Ioc\Container::register('Handler\Log\Generic', function () {
    $handlerMock = $this->createMock('Handler\Log\Generic');
    $handlerMock->expects($this->any())->method('setWorkerId')->will($this->returnValue($handlerMock));
    $handlerMock->expects($this->any())->method('setChId')->will($this->returnValue($handlerMock));

    $handlerMock
        ->expects($this->once())
        ->method('setCommand')
        ->with($this->callback(function ($object) {
            return $object instanceOf \Maleficarum\Command\AbstractCommand;
        }));

    $handlerMock->expects($this->once())->method('handle');

    return $handlerMock;
});
