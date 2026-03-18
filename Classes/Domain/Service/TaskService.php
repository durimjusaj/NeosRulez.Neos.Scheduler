<?php
namespace NeosRulez\Neos\Scheduler\Domain\Service;

/*
 * This file is part of the NeosRulez.Neos.Scheduler package.
 */

use Neos\Flow\Annotations as Flow;

class TaskService
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\Neos\Scheduler\Domain\Repository\TaskRepository
     */
    protected $taskRepository;


    /**
     * @return string
     * @throws \Exception
     */
    public function executeTasks():string
    {
        $executableTasks = $this->taskRepository->findExecutable();
        $result = 'There are no tasks to run.';
        if(!empty($executableTasks)) {
            $result = '';
            foreach ($executableTasks as $executableTask) {
                if(new \DateTime() >= new \DateTime($executableTask['nextexecution']) || $executableTask['nextexecution'] == null) {
                    $this->executeShellScript($executableTask['command']);
                    $this->taskRepository->setExecution($executableTask['persistence_object_identifier']);
                    $result .= 'Task "' . $executableTask['description'] . '" completed 👍' . "\n";
                }
            }
        }
        return $result;
    }

    /**
     * @param string $shellScript
     * @return void
     */
    public function executeShellScript(string $shellScript):void
    {
        shell_exec(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", rawurldecode($shellScript)));
    }

}