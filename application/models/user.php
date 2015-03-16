<?php

/**
 * The User Model
 *
 * @author Faizan Ayubi
 */
class User extends Framework\Model {
    
    /**
     * @column
     * @readwrite
     * @primary
     * @type autonumber
     */
    protected $_id;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_ﬁrst;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     */
    protected $_last;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     */ 
    protected $_email;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 100
     * @index
     */
    protected $_password;
    
    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_notes;
    
    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_live;
    
    /**
     * @column
     * @readwrite
     * @type boolean
     * @index
     */
    protected $_deleted;
    
    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_created;
    
    /**
     * @column
     * @readwrite
     * @type datetime
     */
    protected $_modiﬁed;

}
