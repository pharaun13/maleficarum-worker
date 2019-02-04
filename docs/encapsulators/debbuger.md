# [Encapsulator - Debugger] 
### Basic info

This encapsulator is created for easier code debugging, especially when you want to discover each part of your code has the worst performance. 

### How to use it?
Add `\Maleficarum\Worker\Handler\Encapsulator\Debugger` to the Handler encapsulator list.
```
    use \Maleficarum\Worker\Handler\Encapsulator\Debugger\DebugTrait;

    /**
     * @inheritdoc
     */
    protected function getEncapsulators(): array {
        return \array_merge(
            parent::getEncapsulators(),
            [
                \Maleficarum\Worker\Handler\Encapsulator\Deadletter::class,
                \Maleficarum\Worker\Handler\Encapsulator\Debugger::class
            ]
        );
    }
```

Now in the Handler code you can set up config options: 
* `timeAfterPrintToLog` - set time in seconds when debug information will be printed to log 
* `memoryUsageAfterPrintToLog` - set memory usage in MB when debug information will be printed to log  

This parameters can be set up independently. It is enough for only one condition is met. 

```
    /**
     * @inheritdoc
     */
    public function handle(): bool {                
        $this->setTimeAfterPrintToLog(0); //Print to log everything despite of processing task time.                
        
        $command = $this->getCommand();
        if (!$command->validate()) {
            $this->getLogger()->log('Command: ' . $command->getType() . 'is invalid.','error');

            return false;
        }

    ...
```
An example of handle method with debug points:
```
    /**
     * @inheritdoc
     */
    public function handle(): bool {
        $this->setTimeAfterPrintToLog(0);
        $command = $this->getCommand();
        if (!$command->validate()) {
            $this->getLogger()->log('Command: ' . $command->getType() . 'is invalid.','error');

            return false;
        }

        try {
            $this->debug('Start');
            $lockManager = \Maleficarum\Ioc\Container::retrieveShare('LockManager');
            $client = \Maleficarum\Ioc\Container::get('SolrClient', ['countryCode' => $command->getCountry()]);
            $coreName = $this->createCoreName();
            $tempCoreName = $coreName . '_shadow';
            $dataService = $this->getDataService();

            // Check if it's first worker
            $lockName = $this->createLockIndexName();
            if (!$lockManager->isLockExisted($lockName)) {
                $lockManager->createLock($lockName, $command->getTotal());
                $client->purgeDocuments($tempCoreName);
            }

            $this->debug('After create lock');
            $items = $dataService->fetchByIds($command->getPrefix(), $this->getCommand()->getIds());
            $this->debug('After fetch data');
            $documents = $this->mapItems($items);
            $this->debug('After map items');
            $client->updateDocuments($tempCoreName, $documents);
            $this->debug('After update documents');

            // Check if it's last worker
            $counter = $lockManager->decrementCounter($lockName, \count($this->getCommand()->getIds()));
            if ($counter === 0) {
                $lockManager->deleteLockLock($lockName);
                $client->swapCores($tempCoreName, $coreName);
            }

            $this->debug('Finish');
        } catch (\Throwable $e) {
            $this->getLogger()->log(\json_encode(
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            ), 'error');

            return false;
        }

        return true;
    }

```

In log file you will see:
```

Jan 30 10:25:06 ... [DEBUG] 1. {"message":"Start","time":"0.00001 sec","memory":"0 MB"}
Jan 30 10:25:06 ... [DEBUG] 2. {"message":"After create lock","time":"0.00024 sec","memory":"0.003403 MB"}
Jan 30 10:25:06 ... [DEBUG] 3. {"message":"After fetch data","time":"0.150623 sec","memory":"20.532898 MB"}
Jan 30 10:25:06 ... [DEBUG] 4. {"message":"After map items","time":"0.499449 sec","memory":"33.137085 MB"}
Jan 30 10:25:06 ... [DEBUG] 5. {"message":"After update documents","time":"4.595115 sec","memory":"38.131447 MB"}
Jan 30 10:25:06 ... [DEBUG] 6. {"message":"Finish","time":"4.721609 sec","memory":"38.131882 MB"}
    
```
When you want to have more custom parameters in log, you can hand on them into debug method as a second parameter.

```
    ...
        $this->debug('After fetch data  ', ['productsCount' => $productsCount]);
    ...
```

In log file you will see:
```
Jan 30 10:25:06 ... [DEBUG] 1. {"message":"After fetch data","time":"0.00001 sec","memory":"0 MB","productsCount": "123"}    
```
