<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Community\GroupController;
use App\Http\Controllers\Community\PostController as PostGroup;
use App\Http\Controllers\Community\UserGroupController;
use App\Http\Controllers\Home\FriendController;
use App\Http\Controllers\Home\PostController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['api']], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::get('/google/redirect', [AuthController::class, 'redirect']);
        Route::get('/google/callback', [AuthController::class, 'callBack']);
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/verify-email/{token}', [AuthController::class, 'confirmEmail']);
    });
    Route::group(['prefix'=>'home', 'middleware'=>['jwt.auth']], function() {
        Route::group(['prefix' => 'posts'], function () {
            Route::get('/', [PostController::class, 'index']);
            Route::get('/detail/{id}', [PostController::class, 'detail']);  /** khi cmt sẽ hiện luôn sau khi enter */
            Route::post('/', [PostController::class, 'store']);
            Route::post('/{id}', [PostController::class, 'update']);
            Route::delete('/{id}', [PostController::class, 'delete']);
        });
        Route::group(['prefix' => 'comments', 'middleware' => ['jwt.auth']], function () {
            Route::get('/post/{post_id}', [CommentController::class, 'list']);
            Route::post('/post/{post_id}', [CommentController::class, 'store']);
            Route::put('/{id}', [CommentController::class, 'update']);
            Route::delete('/{id}', [CommentController::class, 'delete']);
            Route::post('/rep-comment/{parent_id}', [CommentController::class, 'replyComment']);
        });
        Route::get('/friends/fillter', [FriendController::class, 'fillter']); /** chưa sử dụng */
    });
    Route::group(['prefix' => 'friends', 'middleware'=>['jwt.auth']], function () {
        Route::get('/{id}', [FriendController::class, 'list']);
        Route::get('/list-request-add/{id}', [FriendController::class, 'listRequestAddFriend']);
        Route::get('/list-request-add-limit/{id}', [FriendController::class, 'listRequestAddFriendLimit']);
        Route::get('/list-suggest/{id}', [FriendController::class, 'listSuggest']);
        Route::get('/detail/{id}', [FriendController::class, 'detail']); /** chưa sử dụng */
        Route::get('/request-add-friend/{friend_id}', [FriendController::class, 'requestAddFriend']);
        Route::put('/confirm-add-friend/{id}', [FriendController::class, 'confirmAddFriend']);
        Route::put('/cancel-request-add-friend/{id}', [FriendController::class, 'cancelRequestAddFriend']);
        Route::get('/remove-friend/{friend_id}', [FriendController::class, 'removeFriend']);
    });
    Route::group(['prefix' => 'users', 'middleware'=>['jwt.auth']], function () {
        Route::get('/me', [UserController::class, 'me']);
        Route::get('/{id}', [UserController::class, 'profile']);
        Route::get('/', [UserController::class, 'list']);
        Route::post('/update-info', [UserController::class, 'updateInfo']);
        Route::get('/images/avatar/{filename}', [UserController::class, 'showAvatar']);

    });
    Route::group(['prefix' => 'groups', 'middleware'=>['jwt.auth']], function () {
        Route::get('/list-all', [GroupController::class, 'listAll']);
        Route::get('/', [GroupController::class, 'myGroups']);
        Route::get('/detail/{id}',[GroupController::class, 'detail']);
        Route::get('/{id}/members', [GroupController::class, 'listMember']);
        Route::get('/joined', [GroupController::class, 'groupJoined']);
        Route::post('/', [GroupController::class, 'store']);
        Route::post('/{id}', [GroupController::class, 'update']);
        Route::delete('/{id}', [GroupController::class, 'delete']);
        Route::get('add-request-join/{id}', [GroupController::class, 'requestJoin']);
        Route::get('cancel-request-join/{id}', [GroupController::class, 'cancelRequestJoin']);
        Route::get('confirm-join/{group_id}/{user_id}', [GroupController::class, 'confirmJoin']); /** chưa sử dụng */
        Route::get('leave/{id}', [GroupController::class, 'leave']);
        Route::get('please-leave/{group_id}/{user_id}', [GroupController::class, 'pleaseLeave']);
        Route::put('{group_id}/set-role/{user_id}', [GroupController::class, 'setRole']);
        Route::get('/feed', [PostController::class, 'listPostGroupJoined']);
        Route::get('{group_id}/list-request-join', [GroupController::class, 'listRequestJoin']);
        Route::get('confirm-put-in-group/{id}', [GroupController::class, 'confirmPutInGroup']);
        Route::get('do-not-put-in-group/{id}', [GroupController::class, 'doNotPutInGroup']);
        Route::group(['prefix' => '{group_id}/posts'], function () {
            // Route::get('/', [PostGroup::class, 'index']);
            Route::post('/', [PostGroup::class, 'store']);
            Route::post('/{post_id}', [PostGroup::class, 'update']);
            Route::delete('/{id}', [PostGroup::class, 'delete']);
        });
        Route::group(['prefix' => 'comments'], function () {
            Route::post('/post/{post_id}', [CommentController::class, 'store']);
            Route::put('/{id}', [CommentController::class, 'update']);
            Route::delete('/{id}', [CommentController::class, 'delete']);
            Route::post('/rep-comment/{parent_id}', [CommentController::class, 'replyComment']);
        });
    });
    Route::group(['prefix' => 'like', 'middleware'=>['jwt.auth']], function () {
        Route::post('/{post_id}', [LikeController::class, 'likeAndUnlike']);
    });
    Route::group(['prefix' => 'notifies', 'middleware'=>['jwt.auth']], function () {
        Route::get('/', [NotifyController::class, 'list']);
        Route::get('/read/{id}', [NotifyController::class, 'read']);
    });
    Route::group(['prefix' => 'chat', 'middleware'=>['jwt.auth']], function () {
        Route::get('/rooms', [ChatController::class, 'rooms']);
        Route::get('/room/{roomId}/messages', [ChatController::class, 'message']);
        Route::post('/room/{roomId}/message', [ChatController::class, 'newMessage']);
        //chat user
        Route::get('/get-chats',[ChatController::class, 'getChats']);
        Route::post('/create-chat',[ChatController::class, 'createChat']);
        Route::get('/get-chat-by-id/{chat}',[ChatController::class, 'getChatById']);
        Route::post('/send-text-message',[ChatController::class, 'sendTextMessage']);
        Route::post('/search-user',[ChatController::class, 'searchUsers']);
        Route::get('/message-status/{message}',[ChatController::class, 'messageStatus']);
    });
});
