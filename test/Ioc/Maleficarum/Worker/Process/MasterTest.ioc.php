<?php

\Maleficarum\Ioc\Container::registerBuilder('Maleficarum\Worker\Process\Master', function () {
    $loggerMock = $this->createMock('Maleficarum\Worker\Logger\Logger');
    $rabbitConnectionMock = $this->createMock('Maleficarum\Rabbitmq\Connection\Connection');
    $rabbitManagerMock = $this->createMock('Maleficarum\Rabbitmq\Manager\Manager');
    $channelMock = $this->createMock('PhpAmqpLib\Channel\AMQPChannel');
    $connectionMock = $this->createMock('PhpAmqpLib\Connection\AMQPStreamConnection');

    // testMainLoop
    if ($this->getContext() === 'testMainLoop') {
        $rabbitConnectionMock
            ->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($channelMock));

        $rabbitConnectionMock
            ->expects($this->once())
            ->method('getQueueName')
            ->will($this->returnValue('test-queue'));

        $channelMock
            ->expects($this->once())
            ->method('basic_consume')
            ->with($this->equalTo('test-queue'));

        $connectionMock
            ->expects($this->once())
            ->method('getSocket')
            ->will($this->returnValue(\stream_socket_server('tcp://127.0.0.1:8000')));

        $rabbitConnectionMock
            ->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $rabbitManagerMock
            ->expects($this->exactly(2))
            ->method('fetchSources')
            ->will($this->returnValue([[$rabbitConnectionMock]]));
    }

    return (new \Maleficarum\Worker\Process\Master())
        ->setLogger($loggerMock)
        ->setQueue($rabbitManagerMock);
});

\Maleficarum\Ioc\Container::registerBuilder('PhpAmqpLib\Message\AMQPMessage', function () {
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

\Maleficarum\Ioc\Container::registerBuilder('Handler\Log\Generic', function () {
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
