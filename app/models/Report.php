<?php

class Report extends Eloquent{
    
    protected $table = 'report';
    
    protected $fillable = array(
        'reported_by',
        'type',
        'target'
    );
}
