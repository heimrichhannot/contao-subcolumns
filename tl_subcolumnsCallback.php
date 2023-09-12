<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 *
 * PHP version 5
 * @copyright  Felix Pfeiffer : Neue Medien 2007 - 2012
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @package    Subcolumns
 * @license    CC-A 2.0
 */


/**
 * Class tl_subcolumnsCallback
 *
 * Provides a callback function for copying articles or pages
 * @copyright  Felix Pfeiffer : Neue Medien 2010
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @package    Subcolumns
 * 
 */
class tl_subcolumnsCallback extends Backend
{
	/*
	 * Get all sets from the configuration array
	 */
	public function getSets()
	{
		$arrSets = array();
		
		foreach($GLOBALS['TL_SUBCL'] as $k=>$v)
		{
			$arrSets[$k] = $v['label'];
		}
		
		return $arrSets;
	}
	
	
	public function pageCheck($intId=0)
	{
		if($intId==0) return '';
		
		if(!$this->Input->get('childs'))
		{
			$objArticle = $this->Database->prepare("SELECT id FROM tl_article WHERE pid=?")
										 ->execute($intId);
			if($objArticle->numRows > 0)
			{
				while($objArticle->next())
				{
					$this->copyCheck($objArticle->id);
				}
			}
		}
		else if($this->Input->get('childs') == 1)
		{
			$arrPages = $this->getChildRecords($intId,'tl_page');
			
			foreach($arrPages as $id)
			{
				$objArticle = $this->Database->prepare("SELECT id FROM tl_article WHERE pid=?")
										 ->execute($id);
				
				if($objArticle->numRows > 0)
				{
					while($objArticle->next())
					{
						$this->copyCheck($objArticle->id);
					}
				}
			}
			
		}
	}

	public function articleCheck($intId=0)
	{
		if($intId==0) return '';
		$this->copyCheck($intId);
		
	}
	
	/**
     * 
     * HOOK: $GLOBALS['TL_HOOKS']['clipboardCopyAll']
     * 
     * @param array $arrIds
     */
    public function clipboardCopyAll($arrIds)
    {
        $arrIds = array_keys(array_flip($arrIds));
		
		$objDb = $this->Database->execute("SELECT DISTINCT pid FROM tl_content WHERE id IN (".implode(',',$arrIds).")");
		
		if($objDb->numRows > 0)
		{
			while($objDb->next())
			{
				$this->copyCheck($objDb->pid);
			}
		}
		
    }

	/**
     * Copy a colset
     * 
     * @param integer $pid
     */
	public function copyCheck($pid)
	{
		$row = $this->Database->prepare("SELECT id, sc_childs, sc_parent FROM tl_content WHERE pid=? AND type=? ORDER BY sorting")
			->execute($pid, 'colsetStart');

		if ($row->numRows < 1) return;

		$typeToNameMap = [
			"colsetStart" => "Start",
			"colsetPart" => "Part",
			"colsetEnd" => "End"
		];

		while ($row->next()) {

			$parent = $row->id;
			$oldParent = $row->sc_parent;
			$newSCName = "colset.$row->id";
			$oldChilds = unserialize($row->sc_childs);

			if (!is_array($oldChilds)) continue;

			$this->Database->prepare("UPDATE tl_content %s WHERE pid=? AND sc_parent=?")
				->set(['sc_parent' => $parent])
				->execute($pid, $oldParent);

			$child = $this->Database->prepare("SELECT id, type FROM tl_content WHERE pid=? AND sc_parent=? AND id!=? ORDER BY sorting")
				->execute($pid, $parent, $parent);

			if ($child->numRows < 1) continue;

			$childIds = [];
			while ($child->next()) {
				$childIds[] = $child->id;
				$childTypes[$child->id] = $child->type;
			}
			sort($childIds);

			$this->Database->prepare("UPDATE tl_content %s WHERE id=?")
				->set(['sc_name' => $newSCName, 'sc_childs' => $childIds])
				->execute($parent);

			$partNum = 1;
			foreach ($childTypes as $id => $type) {
				$newchildSCName = $newSCName . "-$typeToNameMap[$type]" . ($type === "colsetPart" ? "-" . $partNum++ : '');
				$this->Database->prepare("UPDATE tl_content %s WHERE id=?")
					->set(['sc_name' => $newchildSCName])
					->execute($id);
			}

		}

	}
	
	public function formCheck($intId,DataContainer $dc)
	{
		if($intId==0) return '';
		
		$objElements = $this->Database->prepare("SELECT id,fsc_parent FROM tl_form_field WHERE pid=? AND type=?")->execute($intId,'formcolstart');
		
		if($objElements->numRows == 0) return '';
		
		while($objElements->next())
		{
			$strName = 'colset.' . $objElements->id;
			$this->Database->prepare("UPDATE tl_form_field %s WHERE pid=? AND fsc_parent=?")
						   ->set(array('fsc_parent'=>$objElements->id,'fsc_name'=>$strName))
						   ->execute($intId,$objElements->fsc_parent);
			
			$objParts = $this->Database->prepare("SELECT * FROM tl_form_field WHERE fsc_parent=? AND type!=? ORDER BY fsc_sortid")->execute($objElements->id,'formcolstart');
			
			$arrChilds= array();
			
			while($objParts->next())
			{
				
				$strName = $objParts->type == 'formcolend' ? 'colset.' . $objElements->id . '-End' : 'colset.' . $objElements->id . '-Part-' . $objParts->fsc_sortid;
				$this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
						   ->set(array('fsc_name'=>$strName))
						   ->execute($objParts->id);
				
				$arrChilds[] = $objParts->id;
			}
			
			$this->Database->prepare("UPDATE tl_form_field %s WHERE id=?")
						   ->set(array('fsc_childs'=>$arrChilds))
						   ->execute($objElements->id);
		
		}
	
	}

}

?>
