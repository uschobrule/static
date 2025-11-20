<?php

namespace App\Http\Controllers;
use Response;

class AppTestFaqController extends AppController
{	
	public function missingMethod($uri = array())
	{	
		# parse_uri
		return $this->faq($uri);
	}

	public function faq($device,$refreshTime = 0, $req = 0) {

        $data = [
                    [
                        'title' => "How do I get to the Fan Forum?",
                        'text' => "Click on the USCHO tab in bottom right corner and you will see a link to USCHO.com and fanforum.USCHO.com.",
                        'height' => "100.0",
                        'url' => ""
                    ],

                    [
                        'title' => "I am confused on how to navigate the app.",
                        'text' => "There are five tabs at the bottom of the screen. The App loads on the Scores tab. Click on a tab at the bottom to get to the feature. There is also an icon with three dots in the top banner that brings up a dropdown for Settings, Policy and the FAQ.",
                        'height' => "150.0",
                        'url' => "https://i0.wp.com/www.uscho.com/wp-content/uploads/2019/10/".$device."FAQMainNav.png"
                    ],

                    [
                        'title' => "How do I get to Women’s Hockey, Men’s DIII, …",
                        'text' => "The area with a red background is a dropdown menu. Click it and select the division gender you want.",
                        'height' => "100.0",
                        'url' => ""
                    ],


                    [
                        'title' => "I only see one game in the scores tab. Where are they?",
                        'text' => "The scores tab swipes right and left. The day’s games are broken up into conference games, non-conference games and exhibition games. The tabs slide right and left and you can select a conference or swipe to the conference.",
                        'height' => "125.0",
                        'url' => "https://i0.wp.com/www.uscho.com/wp-content/uploads/2019/10/".$device."FAQGameDay.png"
                    ],

                    [
                        'title' => "How do I pick a different date?",
                        'text' => "You can use the Previous or Next buttons to move by one date, or simply click on the data and a calendar pops up. You can only select a date where there are games.",
                        'height' => "125.0",
                        'url' => ""
                    ],

                    [
                        'title' => "How do I find my team?",
                        'text' => "Click on the teams tab and just like game day, you can swipe to your conference or select the conference from the horizontal scroll by clicking. Then click the default team for that conference and a drop down appears with all teams. You can also click on a team in the standings and rankings tab and it will bring you straight to the schedule tab for the team.",
                        'height' => "200.0",
                        'url' => "https://i0.wp.com/wwwtest.uscho.com/wp-content/uploads/2019/10/".$device."FAQFindTeam.png"
                    ],


                    [
                        'title' => "How do I select my favorite team?",
                        'text' => "Go to the Teams tab, select the conference, select the team by clicking on the default team name, pick your team. Then click on the +FAVORITE link. It will turn green. By selecting your favorite team you can configure notifications. Also the app highlights them in green on opponents schedules. The Scores tab should default to the tab your favorite team is on for that night. ",
                        'height' => "200.0",
                        'url' => "https://i0.wp.com/www.uscho.com/wp-content/uploads/2019/10/".$device."FAQFavTeam.png"
                    ],


                    [
                        'title' => "How do I get notifications?",
                        'text' => "Select a favorite team. Then click the 3 dots in the upper right and select settings. Agree to accept notifications and configure the type of notifications you want.",
                        'height' => "150.0",
                        'url' => ""
                    ],

                    [
                        'title' => "Can we get rid of the ad on the splash page?",
                        'text' => "In order to help pay for all the content USCHO provides for free, we need to display ads. Apps do not have good locations for ads so we added a splash page that does not show very often. We may create a paid, ad-free version of the app and the website at a later date, but we do not have a timeline for that project to complete. If there are specific ads that are obnoxious, we can try to ban them. Just notify us of a site issue.",
                        'height' => "300.0",
                        'url' => ""
                    ]
                ];

        return Response::json(array('success' => 1, 'data' => $data, 'refeshTime' => 0));
    }
}
