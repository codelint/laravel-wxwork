<?php

namespace Com\Codelint\WxWork\Console\Console\Commands;

use Com\Codelint\WxWork\Laravel\Facade\CorpAgent;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test {case=mail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addOption('uid', 'u', InputOption::VALUE_OPTIONAL, '', 'gzhang');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = CorpAgent::sdk()->department(2);

        $this->info(env('CORP_ID'));
        $this->info(json_encode($users));

        return Command::SUCCESS;
    }
}
