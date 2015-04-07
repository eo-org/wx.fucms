<?php
namespace Application;

class SiteInfo
{
	protected static $websiteId;
	
	static public function setWebsiteId($websiteId)
	{
		self::$websiteId = $websiteId;
	}
	
	static public function getWebsiteId()
	{
		return self::$websiteId;
	}
}