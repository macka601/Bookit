<?php

class items extends DataObject {

  	private static $db = array(
        'Item' => 'Varchar',
        'Description' => 'Text',
		'AssetNumber' => 'Text',
		'MaxBookOutPeriod' => 'Int'
    );

    private static $has_one = array(		
		'ItemPhoto' => 'Image',
		'itemsPage' => 'itemsPage',
    );  

	// Summary fields for items
	public static $summary_fields = array(
		'AssetNumber' => 'Text',
		'ItemPhoto.StripThumbnail' => 'Image', 
		'Item' => 'Item',		
		'formattedQuote' => 'Description'
	);		

	function formattedQuote(){
		$quote = $this->Description;
		if( strlen($quote) < 140 ){
			return $quote;
		}else{
			return substr( $quote, 0, 140 )."...";
		}
	}
		
	// This returns the CMS fields for the item in the grid table
	public function getCMSFields() {
		// Profile picture field	
		$thumbField = new UploadField('ItemPhoto', 'Item picture');

		$thumbField->allowedExtensions = array('jpg', 'png', 'gif');
	
		// Instigate a item_booking object new object
		$itemBookingHistory = new item_booking();
		
        $itemBookingHistory = item_Booking::get()->filter(array(
				'ItemID' => $this->ID
		));				
		
		$gridFieldConfig = GridFieldConfig::create()->addComponents(
				  new GridFieldToolbarHeader(),
				  new GridFieldSortableHeader(),
				  new GridFieldDataColumns(),
				  new GridFieldPaginator(10),
				  new GridFieldEditButton(),
				  new GridFieldDeleteAction(),
				  new GridFieldDetailForm()
		);
		
		$gridField = new GridField('pages', 'Item Booking History', $itemBookingHistory, $gridFieldConfig); 
		
		$dataColumns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns');		
		
		$dataColumns->setDisplayFields(array(			
			'itemBookingFrom'=> 'Booked Item From',
			'itemBookingTo'=> 'Booked Item To',
			'BookedBy.Name' => 'Item Booked By',			
			'ItemReturnedNice' => 'Item Returned',
			'itemReturnDate' => 'Item Return Date'
		));
		
		// Cms fields to return
		return new FieldList(		  
			new TextField('AssetNumber', 'Item Asset Number'),
			new TextField('Item', 'Item'),
			new TextareaField('Description', 'Item Description'),
			new NumericField('MaxBookOutPeriod', 'Maximum book out period (Days)'),
			$thumbField,
			$gridField
		);
	}
   	
	public function Link()
	{		
		if($itemsPage = $this->itemsPage())
		{			
			$Action = 'show/' . $this->ID;
			return $itemsPage->Link($Action);
		} 	
	}
}

class item_booking extends DataObject {

  	private static $db = array(
		'itemBookingFrom' => 'Date',
		'itemBookingTo' => 'Date',
		'itemReturnDate' => 'Date',		
		'itemReturned' => 'Boolean',
		'cancelUserBookingHistory' => 'Boolean'
    );
	
    private static $has_one = array(		
		'Item' => 'Item',		
		'BookedBy' => 'Member'
	);

	//Generate Yes/No for CMS Fields
	public function ItemReturnedNice() { 
		return $this->itemReturned ? 'Yes' : 'No'; 
	} 

	// This returns the CMS fields for editing the item booking history
	public function getCMSFields() {
		$member = Member::get()->byID($this->BookedByID);
				
		$name = $member->FirstName." ".$member->Surname;
	
		// Cms fields to return
		return new FieldList(		  		
			new ReadonlyField('text', 'Item Booked By',$name),
			new TextField('itemBookingFrom', 'Item Booked From'),
			new TextField('itemBookingTo', 'Item Booked To'),
			new CheckboxField('itemReturned', 'Item returned')
		);
	}
	
	public function ReturnItemLink() {			
			
		$CurrentPage = Director::get_current_page()->Link();
		
		$Action = $CurrentPage.'ReturnItem/' . $this->ID;	
				
		return $Action;
		
	}		
	
	public function CancelBookingLink() {			
			
		$CurrentPage = Director::get_current_page()->Link();
		
		$Action = $CurrentPage.'CancelBooking/' . $this->ID;	
				
		return $Action;
		
	}		
}

class items_Controller extends Page_Controller {
	
}