<?php


/**
 * User Profile Pages   (@Module)
 *
 * @Module  (Profile)
 * @Module  (Profile Uploads)
 * @Module  (Profile Comments)
 * @Module  (Profile Upvotes)
 * @Module  (Profile Badges)
 */
 
/**
 * @Module  (Profile)
 */
Route::get('/s/{username}', array(
    'as'    => 'user-profile',
    'uses'  => 'ProfileController@userProfile'
));

/**
 * @Module  ( Profile Uploads)
 */
Route::get('/s/{username}/uploads', array(
    'as'    => 'user-profile',
    'uses'  => 'ProfileController@userProfile'
));

/**
 * @Module  (Profile Upvotes)
 */
Route::get('/s/{username}/upvotes', array(
    'as'    => 'user-profile-upvotes',
    'uses'  => 'ProfileController@userProfileUpVotes'
));

/**
 * @Module  (Profile Comments)
 */
Route::get('/s/{username}/comments', array(
    'as'    => 'user-profile-comments',
    'uses'  => 'ProfileController@userProfileComments'
));

/**
 * @Module  (Profile Badges)
 */
Route::get('/s/{username}/badges', array(
    'as'    => 'user-profile-badges',
    'uses'  => 'ProfileController@userProfileBadges'
));


Route::get('/s/{username}/badges/{type}', array(
    'as'    => 'user-profile-badges-types',
    'uses'  => 'ProfileController@awards'
));


Route::group(array('before' => 'auth'), function(){
    Route::get('/post/suggestions', array(
        'as'    => 'post-tags-suggestions',
        'uses'  => 'ContentController@suggestions'
    ));
    
    Route::post('/post/suggestions/accept', array(
        'as'    => 'post-tags-suggestions-accept',
        'uses'  => 'ContentController@suggestionsAccept'
    ));

    Route::post('/post/suggestions/decline', array(
        'as'    => 'post-tags-suggestions-decline',
        'uses'  => 'ContentController@suggestionsDecline'
    ));
});
/**
 * @Module  (user follow subscriptions)
 */
Route::get('/s/{username}/followers', array(
    'as'    => 'follow-user',
    'uses'  => 'ProfileController@followUser'
));

/**
 * @Module  (user subscriptions summary)
 */
Route::group(array('before' => 'auth'), function(){
    Route::get('/post/subscription_summary', array(
        'as'    => 'subscription-summary',
        'uses'  => 'ContentController@subscriptionSummary'
    ));
    Route::get('/post/subscriptions/accept', array(
        'as'    => 'subscription-accept',
        'uses'  => 'ContentController@subscriptionAccept'
    ));

    Route::get('/post/subscriptions/decline', array(
        'as'    => 'subscription-decline',
        'uses'  => 'ContentController@subscriptionDecline'
    ));
    
});

/**
 * @Module  (block user)
 */
Route::get('/s/{username}/block', array(
    'as'    => 'block-user',
    'uses'  => 'ProfileController@blockUser'
));

/**
 * @Module  (user reports)
 */
Route::get('/s/{username}/post_reports', array(
    'as'    => 'block-user',
    'uses'  => 'ProfileController@postReports'
));


?>
