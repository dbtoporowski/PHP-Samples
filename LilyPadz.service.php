<?php
/*
 * The class manages LilyPadz
 * 
 * 
 * @created January, 2011
 * 
 */
 
require_once('../LIB/LilyPadz/LilyPadz.class.php');
require_once('../LIB/SocialNetworking/SocialNetworking_Badges.class.php');

class LilyPadz extends API {
	
	// methods 
	/*
	* getGameInfo
	* storeScore
	* awardBadge
	*/
		
        
    /**
     * Main function.  This returns all the information needed by the game to setup the levels
     * and decide what badges (if any) to award.
     * 
     * RPC(1000,"LilyPadz::getGameInfo")
     * 
     * @return mixed
    */
	public static function getGameInfo() {
		
		// Create new instance of this class.
		$pLily = new LilyPadzAPI();
		$return = "";
		// Fetch all the level information for all levels.
		$results = $pLily->getLevels();
		
		// Check if this user has already been awarded badges.
		if ($pLily->hasAward('SN_AWARD_LILYPADZ_TREE_FROG')) {		
			$treeFrogAwardEarned 				= 1;
		} else {		    
			$treeFrogAwardEarned 				= 0;
		}
		
		if ($pLily->hasAward('SN_AWARD_LILYPADZ_DRY_HEIGHTS')) {
			$dryHeightsAwardEarned 			= 1;
		} else {
			$dryHeightsAwardEarned 			= 0;
		}
		
		// Return level information
		$return .= '<LilyLevels>';
		while ($myrow = mysql_fetch_array($results)) {
			$return .= '<level levelNum="'.$myrow['level'].'" 
						   cloudDuration ="'.$myrow['cloudDuration'].'"
						   rainFrequency ="'.$myrow['rainFrequency'].'" />';			
		}
		$return .= '</LilyLevels>';
		
		// Return XML
		pBuffer($return);
		return;
	}

 	/**
     * storeScore method.  Verifies the store stated and saves it to the database.  If the information
     * seems legit, we also award the cash to the player.
     *
     * RPC(1000,"LilyPadz2::storeScore",{"level":"5","score":"30"});
     * 
     * @return null
    */
	public static function storeScore($args = null) {
		$pLily = new LilyPadzAPI();
		$user_DbId 	= $pLily->user_DbId;
		
		$level			= $args['level'];
		$score			= $args['score'];
		
		if ($pLily->num_levels == $level) {
			$fl_winFlag = 1;
		} else {
			$fl_winFlag = 0;
		}
		
		if ($pLily->isValidScore($score)) {
			$cashPayout = $pLily->getPayoutAmount($level, $score);
		} else {
			$cashPayout = 0;
		}
		
		if ($pLily->isValidScore()) {
		    $pLily->setScore($user_DbId, $level, $score, $cashPayout, $fl_winFlag);			
			// Payout the user.
			$pLily->userObj->set_user_money($pLily->userObj->get_user_money()+$cashPayout);
		}
		
		return true;
	}
	
	/**
     * awardBadge method.  Calls the social networking API and tells the system to award this user
     * a badge.  This method will double check that this player does not get the same badge twice.
     *
     * RPC(1000,"LilyPadz2::awardBadge",{"badgeName":"SN_AWARD_LILYPADZ_TREE_FROG"});
     * 
     * @return int
    */
	public static function awardBadge($args = null) {
		$pLily = new LilyPadzAPI();
		$user_DbId = $pLily->user_DbId;
		
		$badgeName = $args['badgeName'];
		SocialNetworking_Badges::UnlockAward($user_DbId, 'badge', $badgeName, true);
	}
	
	public static function awardTag($args = null) {
		$pLily = new LilyPadzAPI();
		$user_DbId = $pLily->user_DbId;
		
		$tagName = $args['tagName'];
		SocialNetworking_Badges::UnlockAward($user_DbId, 'tagline', $tagName, false);
	}
	
	/**
     * 
     *
     * RPC(1000,"LilyPadz2::awardTrophy");
     * 
     * @return int
    */	
	public static function awardTrophy($args = null) {
		$pLily = new LilyPadzAPI();
		$user_DbId = $pLily->user_DbId;
		
		if ($pLily->hasTrophy()) {
			pBuffer('<award response="0" />');
		} else {
		    $trophy = $pLily->getTrophyId();
			$pLily->setAchievements($user_DbId, $trophy);
			pBuffer('<award response="1" />');
		}
	}
	
}
?>