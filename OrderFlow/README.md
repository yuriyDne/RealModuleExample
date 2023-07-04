## OrderFlow module

#### Console commands:
      orderflow:order:testRun {orderId - required} {status - optional} - process test order or changes it's statue          
      orderflow:order:testSend {orderId - required} - Test send order         
      orderflow:queue:clean - clean old queue Items
      orderflow:queue:run - Run orderflow queue process

#### Datasase tables:
* order_flow_queue - consists orders need to be process or order processing failed orders
* order_flow_queue_log - consists orderflow api request logs

#### Separate log files:
* var/log/orderflow/debug.log - debug info
* var/log/orderflow/error.log - error info

### Main Logic Description:
##### Processor statuses configuration:
  - etc/orderflow_status.xml - order statuses processing config. Possible arguments are in etc/orderflow_status.xsd
##### Directories description
- Model - consists data objects and Databsae logic mostly
- Service - consists business logic

#### OrderFlowQueueQun Sequence:
Entry Point - \Fisha\OrderFlow\Service\Queue\Run::execute:
* Locks process to not process orders twice in separate orders (use file storage)
* Adds new items to queue
* Run orders in queue processing
* Removes correctly processed items from queue
* Unlock process

#### Orders in queue processing
Entry point - \Fisha\OrderFlow\Service\Queue\Run::processItemsByStatus
* Groups orders by statuses
* Process each status order group separately
* Uses original order status but not order status in queue
* Run \Fisha\OrderFlow\Service\RunProcessorService::execute for each order

#### Order processing logic:
* Create orderStatusConfig based on order Status and orderflow_status.xml configurations
* Create order processor instance based on orderStatusConfig
* Run \Fisha\OrderFlow\Api\ProcessorInterface::execute(OrderInterface $order): ResultInterface

#### Possible order processing cases:
**Success case**:
* Order moves to next status (Based on ResultInterface or orderStatusConfig)
* Emails is sending to customer and/or admin (if email sending is active for specific status in Magento Admin)

**RestartException** - In case of repeat order processing in current status need to be run (E.g. empty Api request)
* Uses nextRunInMinutes and attemptsCount orders status configs
* Stops order processing in case of attemptsCount reached with `Max attempts count reached` reason

**ProcessFailedStatusException** - In case of order need to be moved to failed status specified in orderflow_status.xml
* Logs error reason
* Stops order processing with `Failed Processor Status` reason
* Emails is sending to customer and/or admin (if email sending is active for specific status in Magento Admin) 

**Exception** - process was stopped by error reason
* Runs a specific retry logic by formula: nextRunInMinutes * attempts count
* If maxAttemptsCount Reached, Stops order processing with `Unknown Reason` reason

### Add new Status Processor Flow
1. Add status configurations to orderflow_status.xml
2. Implement Status Processor based on \Fisha\OrderFlow\Service\Processor\AbstractProcessor
3. Check email sending for a specific processor (`orderflow:order:testSend`  - useful command)

#### Common processors:
* \Fisha\OrderFlow\Service\Processor\MoveToNextStatus - for automatically moving order to next status
* \Fisha\OrderFlow\Service\Processor\ProcessFailedStatus - for failed statuses processing

## Admin panel:
### OrderFlowQueue management

  
