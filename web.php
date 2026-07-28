<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
|
|use App\Http\Controllers\PDFController;

*/
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Controllers\Frontend\SocialController;
use App\Http\Controllers\Frontend\LinkedinController;
use App\Models\UserSocialAccount;
use App\Http\Controllers\Frontend\SocialControllerNew;
use Illuminate\Support\Facades\Http;


Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return 'cache cleared';
});



Route::get('/auth/facebook', [SocialController::class, 'redirectToFacebook'])->name('facebook.login');
Route::get('/auth/facebook/callback', [SocialController::class, 'handleFacebookCallback']);
Route::post('/facebook/publish/', [SocialController::class, 'publishBanner'])->name('facebook.publish')->middleware('auth');;
Route::get('/social/search-places', [SocialController::class, 'searchPlaces']);
//Route::get('/social', [SocialController::class, 'socialConfig'])->name('account.social')->middleware('auth');;

Route::post('/facebook/publish-video/', [SocialController::class, 'shareVideoOnFacebook'])->name('facebook.publish.video')->middleware('auth');



Route::get('/social/facebook/disconnect', [SocialController::class, 'disconnectFacebook'])->name('social.facebook.disconnect')->middleware('auth');;

Route::get('/social/setting', [SocialController::class, 'socialSetting'])->name('social.setting')->middleware('auth');;
Route::get('/post-creation/{shareon}/{banner}', [SocialController::class, 'showPostCreationForm'])->name('social.post.creation')->middleware('auth');

//Route::get('/video-post-creation/{slug}', [SocialController::class, 'videoPostCreation'])->name('reels.post.creation')->middleware('auth');

Route::get('/reels/create/{shareon}/{slug}', [SocialController::class, 'videoPostCreation'])->name('reels.post.creation')->middleware('auth');

Route::get('/marketing/create/{shareOn}/{slug}', [SocialController::class, 'createMarketingVideoPost'])->name('marketing.post.creation')->middleware('auth');

//Instagram
// Instagram Auth TO Do 21-05-2026
Route::get('/auth/instagram', [SocialController::class, 'redirectToInstagram'])->name('instagram.connect');
Route::get('/auth/instagram/callback', [SocialController::class, 'handleInstagramCallback'])->name('instagram.callback');
Route::get('/auth/instagram/disconnect', [SocialController::class, 'disconnectInstagram'])->name('social.instagram.disconnect');

Route::get('/auth/instagram/accounts', [SocialController::class, 'getAccounts'])->name('instagram.accounts');

Route::any('/instagram/publish/', [SocialController::class, 'postToInstagram'])->name('insta.publish')->middleware('auth');

Route::any('/instagram/publish-video/', [SocialController::class, 'postVideoToInstagram'])->name('insta.publish.video')->middleware('auth');

Route::post('/auth/instagram/deauthorize', function () {
    return response()->json(['success' => true]);
})->name('instagram.deauthorize');

Route::post('/auth/instagram/delete', function () {
    return response()->json(['success' => true]);
})->name('instagram.delete');

//To Do need to remove, test APi call for enable instagram_business_content_publish
/*Route::get('/test-instagram-publish', function() {
    $account = UserSocialAccount::where('provider', 'instagram')->first();

    $igId = $account->instagram_account_id;
    $accessToken = $account->access_token;

    // Make test media container call
    $response = Http::asForm()->post("https://graph.instagram.com/v21.0/{$igId}/media", [
        'image_url' => 'https://masterstrokeonline.s3.ap-south-1.amazonaws.com/public/uploads/premiumbanner/1768971026.jpg',
        'caption' => 'Test post',
        'access_token' => $accessToken
    ])->json();

    dd($response);
});*/


//Linkedin
Route::get('/linked-in/authorization', [LinkedinController::class, 'authorization'])->name('linked-in.authorization')->middleware('auth');
Route::get('/linked-in/redirection', [LinkedinController::class, 'redirection'])->name('linked-in.redirection')->middleware('auth');

Route::get('/linkedin/disconnect', [LinkedinController::class, 'disconnect'])->name('linked-in.disconnect')->middleware('auth');
//need to remove
//Route::get('/linkedin/post-text',[LinkedinController::class, 'postText']);

Route::post('/linkedin/publish-banner', [LinkedinController::class, 'postToLinkedin'])->name('linkedin.publish')->middleware('auth');;

Route::post('/linkedin/publish-video/', [LinkedinController::class, 'linkedinPublishVideo'])->name('linkedin.publish.video')->middleware('auth');


Route::get('/post', [LinkedinController::class, 'post'])->name('linked-in.post')->middleware('auth');

Route::get('/linkedin/check-token',   [LinkedinController::class, 'checkToken']);
Route::get('/linkedin/check-scopes',  [LinkedinController::class, 'checkScopes']);
//need to remove
Route::get('/linkedin/profile', [LinkedinController::class, 'getProfile']);
Route::get('/linkedin/check-scopes', [LinkedinController::class, 'checkScopes']);

//=====10-07-2026 Social Share New Method==================
Route::get('/social/setting-new', [SocialControllerNew::class, 'socialSetting'])->name('social.setting_new')->middleware('auth');;
Route::get('/auth/facebook/new', [SocialControllerNew::class, 'redirect'])->name('facebook.login_new');
Route::get('/auth/facebook/new/callback', [SocialControllerNew::class, 'callback']);

//for new test
Route::get('/post-creation-new/{shareon}/{banner}', [SocialControllerNew::class, 'showPostCreationForm'])->name('social.post.creation')->middleware('auth');
Route::any('/instagram/publish/new', [SocialControllerNew::class, 'postToInstagram'])->name('social.insta.publish')->middleware('auth');
Route::post('/facebook/publish/new', [SocialControllerNew::class, 'postToFacebook'])->name('social.facebook.publish')->middleware('auth');


Route::get('/social/facebook-disconnect', [SocialControllerNew::class, 'disconnectFacebook'])->name('facebook.disconnect')->middleware('auth');

//For testing Need to remove
Route::get('/test-publish', function () {
    $page = [
        'id' => '1052449851278473',
        'name' => 'Social test',
        'access_token' => 'EAAglexSoZC2kBRZBewW3Ie9cS6Q7rrLWs9q5ConmfWoW34aNeRhy2ZCDr82rZBc5otMDl2OZCe8JSIVKzE9PcY3D3lZCkBK6hkEHkCtTM3GBhZCYai5J2z52UqJ8pPooAQi8l4ZCsuDzJX5kIHRQx32hsUqYZAGkDNF0Vh6vK5gigHoJ8ZBpuhwMnUpmtai1HUQseRbEER',
        'instagram_business_account_id' => null,
    ];

    $controller = app(\App\Http\Controllers\Frontend\SocialControllerNew::class);
    $result = $controller->postToFacebookPage($page, 'https://picsum.photos/1200/630', 'Test caption');

    dd($result);
});

//For testing Need to remove
Route::get('/test-publish-ig', function () {
    $page = [
        'id' => '1052449851278473',
        'name' => 'Social test',
        'access_token' => 'EAAglexSoZC2kBRZBewW3Ie9cS6Q7rrLWs9q5ConmfWoW34aNeRhy2ZCDr82rZBc5otMDl2OZCe8JSIVKzE9PcY3D3lZCkBK6hkEHkCtTM3GBhZCYai5J2z52UqJ8pPooAQi8l4ZCsuDzJX5kIHRQx32hsUqYZAGkDNF0Vh6vK5gigHoJ8ZBpuhwMnUpmtai1HUQseRbEER',
        'instagram_business_account_id' => '17841407092849911',  //get by http://127.0.0.1:8001/verify-ig-id
    ];

    $controller = app(\App\Http\Controllers\Frontend\SocialControllerNew::class);
    $result = $controller->postToInstagram($page, 'https://picsum.photos/1200/630', 'Test caption from Laravel');

    dd($result);
});

//For testing Need to remove
Route::get('/test-check-ig', function () {
    $pageId = '1052449851278473';
    $pageAccessToken = 'EAAglexSoZC2kBRZBewW3Ie9cS6Q7rrLWs9q5ConmfWoW34aNeRhy2ZCDr82rZBc5otMDl2OZCe8JSIVKzE9PcY3D3lZCkBK6hkEHkCtTM3GBhZCYai5J2z52UqJ8pPooAQi8l4ZCsuDzJX5kIHRQx32hsUqYZAGkDNF0Vh6vK5gigHoJ8ZBpuhwMnUpmtai1HUQseRbEER';

    $response = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v21.0/{$pageId}", [
        'fields' => 'instagram_business_account',
        'access_token' => $pageAccessToken,
    ]);

    dd($response->json());
});
//For testing Need to remove
Route::get('/verify-ig-id', function () {
    //For ameetojha01
    //$pageId = '1052449851278473';
    //$pageAccessToken = 'EAAglexSoZC2kBRZBewW3Ie9cS6Q7rrLWs9q5ConmfWoW34aNeRhy2ZCDr82rZBc5otMDl2OZCe8JSIVKzE9PcY3D3lZCkBK6hkEHkCtTM3GBhZCYai5J2z52UqJ8pPooAQi8l4ZCsuDzJX5kIHRQx32hsUqYZAGkDNF0Vh6vK5gigHoJ8ZBpuhwMnUpmtai1HUQseRbEER'; // full, not truncated
   
    $pageId = '1052449851278473';
    $pageAccessToken ='EAAOl7ADq5JkBRzTf1NAZAHPO9rgk11ZB3Dp17PbveVWGW8EyJxeWBgCmNof3JPzGVbrU2ipnmQwZBo1dAjFbJBUJcGaxPimo0iz8fVxCOSqmG8whaBP7smfZBeNcQI11K4sKQkZBi1LbwMQfgMadFIVZCBOjXk6pXChHp8ukWnP1I2Wagi9JSTDdWCGi1X';
    
    $response = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v21.0/{$pageId}", [
        'fields' => 'instagram_business_account{id,username}',
        'access_token' => $pageAccessToken,
    ]);

    dd($response->json());
});


//For testing Need to remove
Route::get('/list-my-pages', function () {

    $longLivedToken = 'EAANNRYVmgMkBR8ZAztMcSM5Q5McAGyFUfvGJcPOqDKcjrq3QbFEW2LveCRPEipWm7H1W7XwrTSrPGqfXN5Tv5ix4ZCSw9rxZBsWS08BSzGbisbIfrsurZAzAZCV0wWZC7jBQXjEiUmQq4ACIGfvfwziJDfU02bamHjG1GWXGqDIsZA8KTS1j5zZBN5hKsrHCDQkvdSvv9C1AJQkePcdkmZCS2bh3ioxzLvP6dWTLBr0oXEZBMY0ZC6ivqKLF8xKaY70mv8ZBe7gUzgm4EMZALVPXrZB655RpWt'; // your user token

    $pageIds = ['1069011032963606', '994975843704314'];

    $results = [];
    foreach ($pageIds as $id) {
        $response = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v21.0/{$id}", [
            'fields' => 'id,name,access_token,instagram_business_account',
            'access_token' => $longLivedToken,
        ]);
        $results[] = $response->json();
    }

    dd($results);
});

//==============