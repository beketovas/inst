<?php

namespace Modules\Integration\Console;

use Illuminate\Console\Command;
use Modules\Integration\Entities\Node;
use Modules\Integration\Repositories\NodeRepository;
use Modules\Application\Entities\Application;

class NodeApplicationTypeMigration extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'integration:node-application-type-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';



    protected $nodeRepository;

    /**
     * Create a new command instance.
     *
     * @param NodeRepository $nodeRepository
     */
    public function __construct(NodeRepository $nodeRepository)
    {
        parent::__construct();

        $this->nodeRepository = $nodeRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $nodes = $this->nodeRepository->getAll(['applicationTypeIsNUll' => true]);

        if(!count($nodes)) {
            exit("No nodes.");
        }

        $nodes->each(function(Node $node, $key) use(&$data) {
            $application = Application::find($node->application_id);
            if($application) {
                $node->application_type = $application->type;
                $node->save();
            }
        });
    }

}
