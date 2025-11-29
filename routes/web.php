<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// no cache 0 
Route::group(['middleware' => ['lscache:max-age=0;public']], function () {
    Route::get('json/rankings/d-{division}-{full_gender}-pollemail', [App\Http\Controllers\RankingsController::class,'pollemail']);
    Route::get('json/rankings/d-{division}-{full_gender}-pollimage', [App\Http\Controllers\RankingsController::class,'pollimage']);
    Route::post('json/stats/savepivot', [App\Http\Controllers\StatsPivotController::class,'savepivot']);

    Route::get('jsontest/scoreboard/division-{division}-{full_gender}/{full_season}/gameday', [App\Http\Controllers\ScoreboardTestController::class,'gameday']);
    Route::get('jsontest/scoreboard/division-{division}-{full_gender}/{full_season}/gameday/{gamedate}/{refreshTime}', [App\Http\Controllers\ScoreboardTestController::class,'gameday_date']);
    Route::get('jsontest/scoreboard/division-{division}-{full_gender}/{full_season}/scores/{gamedate}/{refreshTime}', [App\Http\Controllers\ScoreboardTestController::class,'scores']);

    Route::get('json/rankings/npi2/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'npi2']);
    Route::get('json/rankings/rpi2/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'rpi2']);

    Route::get('json/bracket/gconfig/{gender}/{division}/{season}/{game_type_id}/{user_id}', [App\Http\Controllers\BracketController::class,'gconfig']);
    Route::get('json/bracket/gresults/{gender}/{division}/{season}/{game_type_id}/{user_id}', [App\Http\Controllers\BracketController::class,'gresults']);
    Route::get('json/bracket/ncaaseeds/{gender}/{division}/{season}/{game_type_id}/{user_id}', [App\Http\Controllers\BracketController::class,'ncaaseeds']);
    Route::get('json/bracket/pwpseeds/{gender}/{division}/{season}/u_{user_id}', [App\Http\Controllers\BracketController::class,'pwpseeds']);
    Route::post('json/bracket/submit/{gender}/{division}/{season}/{game_type_id}/{user_id}/{type}/{seq}/{serial}', [App\Http\Controllers\BracketController::class,'submit']);
});

// extra long 600
Route::group(['middleware' => ['lscache:max-age=600;public']], function () {
    Route::get('json/rankings/d-{division}-{full_gender}-poll/{date}', [App\Http\Controllers\RankingsController::class,'poll']);
    
    Route::get('json/stats/roster/{code}/{full_gender}-hockey/{full_season}', [App\Http\Controllers\RosterController::class,'roster_season']);
    Route::get('json/stats/overall/division-{division}-{full_gender}/{full_season}', [App\Http\Controllers\StatsController::class,'overall_season']);
    Route::get('json/stats/team/{code}/{full_gender}-hockey/{full_season}', [App\Http\Controllers\StatsController::class,'team_season']);
    Route::get('json/stats/conference/{code}/{full_season}', [App\Http\Controllers\StatsController::class,'conference_season']);

    Route::get('jsonapptest/teaminfo/nav_list', [App\Http\Controllers\AppTestTeamInfo::class,'nav_list']);
    Route::get('jsonapptest/teaminfo/nav_list/', [App\Http\Controllers\AppTestTeamInfo::class,'nav_list']);
    Route::get('jsonapptest/teaminfo/nav_list/{lastModified}', [App\Http\Controllers\AppTestTeamInfo::class,'nav_list_wlogo']);

    Route::get('jsonapptest/faq/{device}', [App\Http\Controllers\AppTestFaqController::class,'faq']);
    Route::get('jsonapptest/faq/{device}/{refreshTime}/{req}', [App\Http\Controllers\AppTestFaqController::class,'faq']);

    Route::get('jsonapp/teaminfo/nav_list', [App\Http\Controllers\AppTeamInfo::class,'nav_list']);
    Route::get('jsonapp/teaminfo/nav_list/', [App\Http\Controllers\AppTeamInfo::class,'nav_list']);
    Route::get('jsonapp/teaminfo/nav_list/{lastModified}', [App\Http\Controllers\AppTeamInfo::class,'nav_list_wlogo']);

    Route::get('jsonapp/faq/{device}', [App\Http\Controllers\AppFaqController::class,'faq']);
    Route::get('jsonapp/faq/{device}/{refreshTime}/{req}', [App\Http\Controllers\AppFaqController::class,'faq']);
});

// LONG 300
Route::group(['middleware' => ['lscache:max-age=300;public']], function () {
    Route::get('json/rankings/allpolls', [App\Http\Controllers\RankingsController::class,'allpolls']);
    Route::get('json/rankings/toppollnew', [App\Http\Controllers\RankingsController::class,'toppollnew']);
    Route::get('json/stats/pivot/division-{division}-{full_gender}/{full_season}/{dataset}', [App\Http\Controllers\StatsPivotController::class,'pivot']);
    Route::get('json/stats/getpivot/{serialtoken}', [App\Http\Controllers\StatsPivotController::class,'getpivot']);

    Route::get('jsonapptest/rankings/poll/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppTestRankingsController::class,'poll']);

    Route::get('jsonapp/rankings/poll/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppRankingsController::class,'poll']);
});

Route::get('json/predictor/seeds/{gender}/{division}/{season}/{ext}', [App\Http\Controllers\PredictorController::class,'seeds']);
Route::get('json/predictor/pwpresults/{gender}/{division}/{season}/{uniqueURI}/{ext}', [App\Http\Controllers\PredictorController::class,'pwpresults']);

// short 30
Route::group(['middleware' => ['lscache:max-age=30;public']], function () {
        //Route::get('json/predictor/seeds/{gender}/{division}/{season}/{ext}', [App\Http\Controllers\PredictorController::class,'seeds']);
    //Route::get('json/predictor/pwpresults/{gender}/{division}/{season}/{uniqueURI}/{ext}', [App\Http\Controllers\PredictorController::class,'pwpresults']);

        Route::get('json/rankings/pairwise-rankings/d-{division}-{full_gender}/current', [App\Http\Controllers\RankingsController::class,'pairwise_rankings']);
    Route::get('json/rankings/pairwise-rankings/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'pairwise_rankings']);
    Route::get('json/rankings/pairwise-rankings2/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'pairwise_rankings2']);
    Route::get('json/rankings/rpi/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'rpi']);
    Route::get('json/rankings/pwrnpi/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'pwrnpi']);
    Route::get('json/rankings/npi/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'npi']);

    Route::get('json/rankings/rpiraw/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'rpiraw']);

    Route::get('json/scoreboard/division-{division}-{full_gender}/{full_season}/gameday', [App\Http\Controllers\ScoreboardController::class,'gameday']);
    Route::get('json/scoreboard/division-{division}-{full_gender}/{full_season}/gameday/{gamedate}/{refreshTime}', [App\Http\Controllers\ScoreboardController::class,'gameday_date']);
    Route::get('json/scoreboard/division-{division}-{full_gender}/{full_season}/scores/{gamedate}/{refreshTime}', [App\Http\Controllers\ScoreboardController::class,'scores']);

    Route::get('jsonapptest/scoreboard/gameday/d-{division}-{full_gender}/{conf}/{gdate}/{game_id}/{refreshTime}/{scrollTime}', [App\Http\Controllers\AppTestScoreboardController::class,'gameday']);
    Route::get('jsonapptest/scoreboard/gameday/d-{division}-{full_gender}/{conf}/{gdate}/{game_id}/{refreshTime}/{scrollTime}/box', [App\Http\Controllers\AppTestScoreboardController::class,'gameday']);
    Route::get('jsonapptest/scoreboard/gamedaybox/d-{division}-{full_gender}/{conf}/{gdate}/{game_id}/{refreshTime}/{scrollTime}/{boxMode}', [App\Http\Controllers\AppTestScoreboardController::class,'gamedaybox']);
    Route::get('jsonapptest/scoreboard/schedule/d-{division}-{full_gender}/{conf}/{team}/{refreshTime}/{scrollTime}', [App\Http\Controllers\AppTestScoreboardController::class,'schedule']);

    Route::get('jsonapptest/notification/{platform}/{token}/{auth_key_in}/{favDiv}/{favTeam}/goal/{goalNotification}/period/{periodNotification}/game/{gameNotification}', [App\Http\Controllers\AppTestNotificationController::class,'sync']);

    //Route::get('jsonapptest/synctokens/{platform}/{token}/{old_token}/{auth_key_in}/{favDiv}/{favTeam}/goal/{goalNotification}/period/{periodNotification}/game/{gameNotification}', [App\Http\Controllers\AppTestSyncController::class,'sync_tokens']);

    Route::get('json/box/{full_gender}-hockey/{year}/{month}/{day}/{match}/{box_view}/{refreshTime}', [App\Http\Controllers\BoxController::class,'box']);
    Route::get('jsontest/box/{full_gender}-hockey/{year}/{month}/{day}/{match}/{box_view}/{refreshTime}', [App\Http\Controllers\BoxTestController::class,'box']);

    Route::get('jsonapptest/rankings/pairwise-rankings/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppTestRankingsController::class,'pairwise_rankings']);
    Route::get('jsonapptest/rankings/rpi/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppTestRankingsController::class,'rpi']);
    Route::get('jsonapptest/rankings/npi/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppTestRankingsController::class,'npi']);

    Route::get('jsonapp/notification/{platform}/{token}/{auth_key_in}/{favDiv}/{favTeam}/goal/{goalNotification}/period/{periodNotification}/game/{gameNotification}', [App\Http\Controllers\AppTestNotificationController::class,'sync']);
    //Route::get('jsonapp/synctokens/{platform}/{token}/{old_token}/{auth_key_in}/{favDiv}/{favTeam}/goal/{goalNotification}/period/{periodNotification}/game/{gameNotification}', [App\Http\Controllers\AppTestSyncController::class,'sync_tokens']);

    Route::get('jsonapp/rankings/pairwise-rankings/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppRankingsController::class,'pairwise_rankings']);
    Route::get('jsonapp/rankings/rpi/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppRankingsController::class,'rpi']);
    Route::get('jsonapp/rankings/npi/d-{division}-{full_gender}', [App\Http\Controllers\RankingsController::class,'npi']);
    Route::get('jsonapp/rankings/npi/d-{division}-{full_gender}/{date}/{refreshTime}/{req}', [App\Http\Controllers\AppTestRankingsController::class,'npi']);
  
    Route::get('jsonapp/scoreboard/gameday/d-{division}-{full_gender}/{conf}/{gdate}/{game_id}/{refreshTime}/{scrollTime}', [App\Http\Controllers\AppScoreboardController::class,'gameday']);
    Route::get('jsonapp/scoreboard/gameday/d-{division}-{full_gender}/{conf}/{gdate}/{game_id}/{refreshTime}/{scrollTime}/box', [App\Http\Controllers\AppScoreboardController::class,'gameday']);
    Route::get('jsonapp/scoreboard/gamedaybox/d-{division}-{full_gender}/{conf}/{gdate}/{game_id}/{refreshTime}/{scrollTime}/{boxMode}', [App\Http\Controllers\AppScoreboardController::class,'gamedaybox']);
    Route::get('jsonapp/scoreboard/schedule/d-{division}-{full_gender}/{conf}/{team}/{refreshTime}/{scrollTime}', [App\Http\Controllers\AppScoreboardController::class,'schedule']);

    Route::get('jsonapp/notification/{platform}/{token}/{auth_key_in}/{favDiv}/{favTeam}/goal/{goalNotification}/period/{periodNotification}/game/{gameNotification}', [App\Http\Controllers\AppNotificationController::class,'sync']);

    //Route::get('jsonapp/synctokens/{platform}/{token}/{old_token}/{auth_key_in}/{favDiv}/{favTeam}/goal/{goalNotification}/period/{periodNotification}/game/{gameNotification}', [App\Http\Controllers\AppSyncController::class,'sync_tokens']);
});

// default cache 120
Route::get('json/rankings/d-{division}-{full_gender}-poll', [App\Http\Controllers\RankingsController::class,'poll']);

Route::get('json/standings/division-{division}-{full_gender}', [App\Http\Controllers\StandingsController::class,'standingsall']);
Route::get('json/standings/division-{division}-{full_gender}/{full_season}', [App\Http\Controllers\StandingsController::class,'standingsall']);
Route::get('json/standings/{conf}/{full_season}', [App\Http\Controllers\StandingsController::class,'standingsconf']);
Route::get('json/standings/{conf}', [App\Http\Controllers\StandingsController::class,'standingsconf']);

Route::get('json/stats/roster/{code}/{full_gender}-hockey', [App\Http\Controllers\RosterController::class,'roster']);
Route::get('json/stats/overall/division-{division}-{full_gender}', [App\Http\Controllers\StatsController::class,'overall']);
Route::get('json/stats/team/{code}/{full_gender}-hockey', [App\Http\Controllers\StatsController::class,'team']);
Route::get('json/stats/conference/{code}/', [App\Http\Controllers\StatsController::class,'conference']);

Route::get('json/bracket/sidebar/{gender}/{division}/{season}/{game_type_id}', [App\Http\Controllers\BracketController::class,'sidebar']);
Route::get('json/bracket/sidebar/{gender}/{division}/{season}/{game_type_id}/u_{user_id}', [App\Http\Controllers\BracketController::class,'sidebar']);
Route::get('json/bracket/leaders/{gender}/{division}/{season}/{game_type_id}', [App\Http\Controllers\BracketController::class,'leaders']);

Route::get('json/topperformers/{gender}/{division}/{season}', [App\Http\Controllers\TopPerformers::class,'top_performers']);

Route::get('json/pickem/sidebar/{gender}/{division}/{season}/{game_type_id}', [App\Http\Controllers\WeeklyPickemLeaders::class,'sidebar']);
Route::get('json/pickem/leaders/{gender}/{division}/{season}/{game_type_id}', [App\Http\Controllers\WeeklyPickemLeaders::class,'leaders']);

Route::get('json/matchup/{gender}/{division}/{full_season}/{game_id}', [App\Http\Controllers\MatchupInfo::class,'matchup']);

Route::get('jsonapptest/standings/d-{division}-{full_gender}/{code}/{refreshTime}/{req}', [App\Http\Controllers\AppTestStandingsController::class,'standings']);

Route::get('jsonapptest/teaminfo/roster/d-{division}-{full_gender}/{conf_code}/{team_code}/{refreshTime}/{req}', [App\Http\Controllers\AppTestTeamInfo::class,'roster']);

Route::get('jsonapp/standings/d-{division}-{full_gender}/{code}/{refreshTime}/{req}', [App\Http\Controllers\AppStandingsController::class,'standings']);

Route::get('jsonapp/teaminfo/roster/d-{division}-{full_gender}/{conf_code}/{team_code}/{refreshTime}/{req}', [App\Http\Controllers\AppTeamInfo::class,'roster']);