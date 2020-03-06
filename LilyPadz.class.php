<?php
/*
 * The class manages LilyPadzAPI
 * 
 * @created January, 2011
 * 
 */


class LilyPadzAPI{
	
	// methods 
	/*
	* __construct
	* __destruct
	* getGameInfo
	* storeScore
	* awardBadge
	* hasAward
	* getBadgeInfo
	* hasTrophy
	* isValidScore
	* getPayoutAmount
	*/
	
    var $user_DbId;
	var $userObj;
	var $trophy_Id;
	var $num_levels;
    
    const MAX_BELIEVEABLE_SCORE = 450;
	 
	/**
     * Class Constructor
    */
	public function __construct() {	
		// set userID, user object and game info
		$this->user_DbId 					= $GLOBALS['user_DbId'];
		$this->userObj 						= $GLOBALS['userObj'];
		$this->trophy_Id					= $this->getTrophyId();
		$this->num_levels					= $this->getNumLevels();
		
		// Bug fix from wrong var coming from Flash
		$GLOBALS['pet_data']['user_DbId'] 	= $this->user_DbId;
		
		// Set world id for SocialNetworking
		$GLOBALS['snctx']['worldId'] 		= $GLOBALS['world_DbId'];	
	}
	
    /**
     * Class Destructor - used for auto-cleaning.
     */
    public function __destruct() {}
    
    /**
     * getLevels method.  Select all available levels.
     * 
     * @return array result
    */
	public function getLevels(){
        $query = 'SELECT * FROM fw_lilyPadzLevels WHERE 1';
		return $GLOBALS['dbInterface']->SQL_select("WORLD_".$GLOBALS['userObj']->world_DbId, $query);
    }
	
	/**
     * getLevels method.  Select all available levels.
     * 
     * @return array result
    */
	public function setScore()
	{
	    $arg_list = func_get_args();
	    list($user_DbId, $level, $score, $cashPayout, $fl_winFlag) = $arg_list;
        $query = "INSERT INTO
						ud_lilyPadzScores
					  SET
					  	user_DbId			= '".$user_DbId."',
						level				= '".$level."',
						score				= '".$score."',
						dateAchieved		= NOW(),
						kinzCashPayout		= '".$cashPayout."',
						time				= UNIX_TIMESTAMP(),
						winFlag				= '".$fl_winFlag."'";
		$results = $GLOBALS['dbInterface']->SQL_select("WORLD_".$GLOBALS['userObj']->world_DbId, $query); 
        return true;		
    }
	
	/**
     * setAchievements method.  Set Achievements Id for giving User.
     * 
     * @return array result
    */
	public function setAchievements()
	{
	    $arg_list = func_get_args();
	    list($user_DbId, $trophy) = $arg_list;
		$query = "INSERT INTO 
							ud_lilyPadzAchievements
						  SET 
							user_DbId	= '".$user_DbId."',
							fw_Achievement_DbId = ". $trophy;
				try {
				  $results = $GLOBALS['dbInterface']->SQL_select("WORLD_".$GLOBALS['userObj']->world_DbId, $query);
				  return true;
				} catch (Exception $e) {
				  pError(0, "The user has already the trophy.");
				  return false;
				}
	}
	
	/**
     * hasAward method.  Checks if an award has been previously unlocked.
     * 
     * @return true/false
    */
	public function hasAward($award) {
		$user_DbId = $this->user_DbId;		
		$awardId = SocialNetworking_Badges::getAwardId($award);
		
		if (!SocialNetworking_Badges::IsAwardUnlocked($user_DbId, 'badge', $awardId)) {			
			return false;
		} else {		    
			return true;	
		}
	}
	
	
	/**
     * hasTrophy method.  Returns if a trophy has been awarded before or not/
     * 
     * @return true/false/
    */
	public function hasTrophy() {
        global $dbInterface;
		$query = 'SELECT * FROM ud_lilyPadzAchievements WHERE user_DbId = '.$this->user_DbId.' AND fw_Achievement_DbId = '. $this->trophy_Id;
		$results = $GLOBALS['dbInterface']->SQL_select("WORLD_".$GLOBALS['userObj']->world_DbId, $query);  
		if (!$results || mysql_num_rows($results) == 0) {
			return false;
		} else {
			return true;	
		}
	}

	/**
     * isValidScore method.  Decides if a score submitted is a valid score.  If not, this API won't
     * award that user the cash or credit for the game.
     * 
     * @return true/false
    */
	public function isValidScore($score) {
		if ($score > self::MAX_BELIEVEABLE_SCORE) {
			return false;
		} else {
			return true;
		}
	}
 
	/**
     * getPayoutAmount method.  Returns the amount of cash to award for a given game.  This is based
     * on score/level.
     * 
     * @return int
    */
	public function getPayoutAmount() {
		return '45';
	}
	
	/**
     * getTrophyId method.  Returns the Trophy id.
     * 
     * @return int
    */
	public function getTrophyId() {
	    //default
		$trophy_id = '3';
		$query = "SELECT * FROM fw_lilyPadzAchievements where description REGEXP '[[:<:]]trophy[[:>:]]'";
		$results = $GLOBALS['dbInterface']->SQL_select("WORLD_".$GLOBALS['userObj']->world_DbId, $query); 
		while ($myrow = mysql_fetch_array($results)) {
		     $trophy_id = $myrow['DbId'];
		}	
        return $trophy_id;		
	}
	
	/**
     * getNumLevels method.  Returns the Levels max.
     * 
     * @return int
    */
	public function getNumLevels() {
	    //default
		$max_levels = '10';
		$query = "SELECT max(level) maxlevel FROM fw_lilyPadzLevels";
		$results = $GLOBALS['dbInterface']->SQL_select("WORLD_".$GLOBALS['userObj']->world_DbId, $query); 
		while ($myrow = mysql_fetch_array($results)) {
		     $max_levels = $myrow['maxlevel'];
		}	
        return $max_levels;		
	}
	
}
?>