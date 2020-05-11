<?php

namespace App\Console\Commands;

use App\Classes\FreeProxyObserverList;
use App\Classes\FreeProxyListParser;
use App\Classes\FreeProxyObserverPagination;
use App\Models\Parser;
use Illuminate\Console\Command;

class FreeProxyListParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'free-proxy-list:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while (!FreeProxyObserverPagination::$lastPaginationPage) {
            FreeProxyListParser::parse(FreeProxyObserverPagination::class);
        }

        $pageId = 0;
        $lastPaginationPage = FreeProxyObserverPagination::$lastPaginationPage;
        while($pageId < $lastPaginationPage) {
            FreeProxyListParser::parse(FreeProxyObserverList::class, $pageId);

            $currentCountRows = count(FreeProxyObserverList::$result);
            if ( // Browsershot don't select rows necessary for first query - go to next page
                $lastPaginationPage == FreeProxyObserverPagination::DEFAULT_PAGINATION_NOT_FOUND_PAGE
                && $currentCountRows
            ) {
                $pageId++;
                FreeProxyObserverList::setPaginationRequest();
                continue;
            }

            if ( // Browsershot don't select rows necessary for next query (without last - be check next) - go to next page
                ($pageId + 1) * 500 == $currentCountRows
            ) {
                FreeProxyObserverList::setPaginationRequest();
                $pageId++;
                continue;
            }

            if ( // last pagination page checking
                $pageId + 1 == $lastPaginationPage && $currentCountRows
            ) {
                FreeProxyObserverList::setPaginationRequest();
                $pageId++;
            }
        }

        if (FreeProxyObserverList::$result) {
            Parser::insert(FreeProxyObserverList::$result);
        }
    }

}
