<?php


Route::group(array('before' => 'admin_auth'), function(){
    
    
    
    /****************************************
     * Admistration Flagged Content (@Module)
     * 
     * @Module  (Flagged Posts)
     * @Module  (Flagged Comments)
     * @Module  (Flagged Replies)
     */
     
    /**
     * @Module  (Flagged Posts)
     */
    Route::any('/9gag-admin/flagged/post',array(
        'as'    =>  'flagged-posts',
        'uses'  =>  'AdminController@flaggedPost'
    ));
      
    /**
     * @Module  (Opted Out Tags)
     */
     Route::any('/9gag-admin/flagged/tags',array(
        'as'    =>  'flagged-posts',
        'uses'  =>  'AdminController@flaggedTags'
    ));
    
    /**
     * @Module  (Flagged Comments)
     */
    Route::any('/9gag-admin/flagged/comments',array(
        'as'    =>  'flagged-comments',
        'uses'  =>  'AdminController@flaggedComments'
    ));
    
    /**
     * @Module  (Flagged Replies)
     */
    Route::any('/9gag-admin/flagged/replies',array(
        'as'    =>  'flagged-replies',
        'uses'  =>  'AdminController@flaggedReplies'
    ));
      /**
     * @Module  (Flagged Profile )
     */
    Route::any('/9gag-admin/flagged/remove-profile',array(
        'as'    =>  'flagged-profile',
        'uses'  =>  'AdminController@removeProfile'
    ));
     /**
     * @Module  (Remove Flagged Profile Pic )
     */
    Route::any('/9gag-admin/flagged/remove-pic',array(
        'as'    =>  'flagged-profile-pic',
        'uses'  =>  'AdminController@removePicture'
    ));
   
});

?>
