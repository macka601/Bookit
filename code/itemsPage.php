<?php

class itemsPage extends Page {

	private static $db = array(
		'CustodianEmailAddress' => 'Text',
		'SendEmailAs' => 'Text',	
		'MessageAfterSuccessfulBooking' => 'Text'
	);  

	// One to many relationship with Contact object
	public static $has_many = array(
		'items' => 'items',
	);
				
    // Create Grid Field
	public function getCMSFields() {
		$fields = parent::getCMSFields();
							
		$gridFieldConfig = GridFieldConfig::create()->addComponents(
		  new GridFieldToolbarHeader(),
		  new GridFieldAddNewButton('toolbar-header-right'),
		  new GridFieldSortableHeader(),
		  new GridFieldDataColumns(),
		  new GridFieldPaginator(10),
		  new GridFieldEditButton(),
		  new GridFieldDeleteAction(),
		  new GridFieldDetailForm()
		);
		
		$gridField = new GridField("items", "Item list:", $this->items(), $gridFieldConfig);

		$fields->addFieldToTab("Root.items", $gridField);
		
		$fields->addFieldToTab("Root.Settings", new TextField('CustodianEmailAddress', 'Custodian Email Address'));
		
		$fields->addFieldToTab("Root.Settings", new TextField('SendEmailAs', 'Email Address Booking Confirmation comes from'));
		
		$fields->addFieldToTab("Root.Settings", new TextField('MessageAfterSuccessfulBooking', 'What to say to user after successful booking'));
				
		return $fields;
	}
}

class itemsPage_Controller extends Page_Controller {
	public static $allowed_actions = array (
		'show',
		'bookItemForm',
		'ReturnItem',
		'CancelBooking',
		'login'
	);
 
    public function init() {
		parent::init();
		
		Requirements::css("BookIt/css/BookIt.css");
		Requirements::css("BookIt/css/lightbox.css");
		Requirements::css("BookIt/javascript/jquery-ui.css");		
		Requirements::javascript("BookIt/javascript/jquery-1.10.2.js");
		Requirements::javascript("BookIt/javascript/jquery-ui.js");
		Requirements::javascript("BookIt/javascript/datePicker.js");
		Requirements::javascript("BookIt/javascript/lightbox.js");		
	}
	
	public function setMessage($type, $message)
    {   
        Session::set('Message', array(
            'MessageType' => $type,
            'Message' => $message
        ));
    }
 
    public function getMessage()
    {
        if($message = Session::get('Message')){
            Session::clear('Message');
            $array = new ArrayData($message);
            return $array->renderWith('Message');
        }
    }
	
	//Get the current Item from the URL, if any
    public function getItems($itemNumber)
    {
        if($itemNumber) {
			$Params = $itemNumber;
		} else {
			$Params = $this->getURLParams();
		}
        
        if(is_numeric($Params['ID']) && $items = $items = items::get()->byID($Params['ID']))
        {   						
            return $items;
        }
    }

    public function bookItemForm() {
		$Params = $this->getURLParams();
		
        // Create fields
        $fields = new FieldList(
            new TextField('from'),
			new TextField('to'),
			new HiddenField('item','',$Params['ID'])
        );
		
        // Create actions
        $actions = new FieldList(
            new FormAction('doBookItemForm', 'Submit')
        );
		
		// List the required fields for the form
     	$validator = new RequiredFields('from', 'to');
				
        return new Form($this, 'bookItemForm', $fields, $actions, $validator);
    }
	
	// Query to get the dates the item has been booked out on
	public function getItemBookedOutDates() {
		
		$Params = $this->getURLParams();
		
		// Instigate a new object
		$itemDatesBookedOutOn = new item_booking();
		
        $itemDatesBookedOutOn = item_Booking::get()->filter(array(
				'ItemID' => $Params['ID'],
				'itemReturned' => 'false'
		));				

		return $itemDatesBookedOutOn;
	}
	
	public function showUserHistoryWithItem() {
		$Params = $this->getURLParams();
		
		// Instigate a item_booking object new object
		$itemBookingHistory = new item_booking();
		
        $itemBookingHistory = item_Booking::get()->filter(array(
				'ItemID' => $Params['ID'],
				'BookedByID' => Member::currentUser()->ID,
				'cancelUserBookingHistory' => false
		));				
		
		return $itemBookingHistory;
	}
		
	// Do the return of an item from the booking page
	function ReturnItem() {		
		
		$item = $this->getURLParams();
		
		$itemBooking = DataObject::get_one('item_booking', '"item_booking"."ID" = '.$item['ID']);		
		
		$itemBooking->itemReturned = true;
				
		$itemBooking->itemReturnDate = date("Y-m-d");
		
		$itemBooking->write();
		
		$this->setMessage('Success', 'Thanks for returning your item');			
	
		// Send out an email about it being returned
		$this->notifyItemHasBeenReturned($itemBooking);
		
		return $this->redirectBack();
	}
	
	// Do the return of an item from the booking page
	function CancelBooking() {		
		
		$item = $this->getURLParams();
		
		$itemBooking = DataObject::get_one('item_booking', '"item_booking"."ID" = '.$item['ID']);		
		
		$itemBooking->cancelUserBookingHistory = true;
		
		if(!$itemBooking->itemReturned) {		
			$this->setMessage('Error', 'Please, return the item before deleting the booking');			
		} else {
			$this->setMessage('Success', 'Your booking has been cancelled');			
			
			$itemBooking->write();
			
			$this->notifyItemBookingDeletion($itemBooking);
		}
		
		return $this->redirectBack();
	}	
	
	function IsUserLoggedIn($member = null) {
		if(Member::currentUser()) {
			return true;
		} else {
			return false;
		}
	}
	
    //Displays the Item detail page, uses the itemsPage_show.ss template
    function show() {    
		//Debug::show($this->canView());
		if($this->IsUserLoggedIn()) {
			if($items = $this->getItems(null))
			{			
				$Data = array(
					'Item' => $items
				);
				
				//return our $Data array to use on the page
				return $this->Customise($Data);
			}		
		}
		
		Security::permissionFailure($this, "You need to be logged in to see this page");
		
		return;
	}
	
	function CheckBookOutPeriod($from, $to) {
		
		$datediff = $to - $from;
		
		return $datediff;
	}
	
	// Puts the data into the database
	function doBookItemForm($data, $form)
	{
		// get the item information
		$item = $this->getItems($data['item']);
		
		// check the requirements
		if($this->CheckBookOutPeriod($data['from'], $data['to']) <= $item->MaxBookOutPeriod) {
			// Make object
			$itemBooking = new item_booking();
			
			// Insert the user that booked the equipment
			$itemBooking->BookedByID = Member::currentUser()->ID;
			
			// List the item that is to be booked out
			$itemBooking->ItemID = $data['item'];
			
			// Item booking date start point
			$itemBooking->itemBookingFrom = $data['from'];
			
			// Item book date end point
			$itemBooking->itemBookingTo = $data['to'];					
			
			$form->saveInto($itemBooking);

			// Write to the database
			$itemBooking->write();
			
			if($messageForUser = $this->MessageAfterSuccessfulBooking) {
				// Get the user message from the CMS
				$this->setMessage("Success", $messageForUser , "good");
			} else {
				// Add default message
				$this->setMessage("Success", "Item Booking Successful" , "good");
			}
			
			// Send notifications to the user and the custodian
			$this->notify($data);		
		} else {
			$this->setMessage("Error", "We are sorry - maximum booking time for this item is ".$item->MaxBookOutPeriod." days, please try again");
		}
		
		return $this->redirectBack();						
	}	
	
	private function notifyItemHasBeenReturned($itemBooked) {
			
		$items = $this->getItems($itemBooked->ItemID);
		
		// Send the User an email about booking
		$email = new Email();
				
		// Subject to put in for user email
		$subjectForEmail = "You have returned the ".$items->Item." - Thanks!";
	
		$member = Member::currentUser();			
		
		$email->setFrom($this->SendEmailAs);
		
		$email->setTo(Member::currentUser()->Email);
		
		$email->setSubject($subjectForEmail);
		
		$email->setTemplate('ItemReturnEmail');					
		
		$email->populateTemplate($member);
		
		$email->populateTemplate($items);
		
		$email->populateTemplate(array(
			'ItemBookedFrom' => $itemBooked->itemBookingFrom,
			'ItemBookedTo' => $itemBooked->itemBookingTo
		));
						
		// Send email
		$email->send();	
	}
		
	private function notifyItemBookingDeletion($itemBooked) {
			
		$items = $this->getItems($itemBooked->ItemID);
		
		// Send the User an email about booking
		$email = new Email();
				
		// Subject to put in for user email
		$subjectForEmail = "Your Booking for ".$items->Item." has been Cancelled";
	
		$member = Member::currentUser();			
		
		$email->setFrom($this->SendEmailAs);
		
		$email->setTo(Member::currentUser()->Email);
		
		$email->setSubject($subjectForEmail);
		
		$email->setTemplate('ItemBookingCancelEmail');					
		
		$email->populateTemplate($member);
		
		$email->populateTemplate($items);
		
		$email->populateTemplate(array(
			'ItemBookedFrom' => $itemBooked->itemBookingFrom,
			'ItemBookedTo' => $itemBooked->itemBookingTo
		));
				
		// Send email
		$email->send();	
	}
	
	// Notifies user and custodian of their bookings
	private function notify ($itemNumberBooked) {
	
		$items = $this->getItems($itemNumberBooked['item']);
		
		// Send the User an email about booking
		$email = new Email();
		
		// Subject to put in for user email
		$subjectForEmail = "Booking Request for ".$items->Item;
	
		$member = Member::currentUser();			
		
		$email->setFrom($this->SendEmailAs);
		
		$email->setTo(Member::currentUser()->Email);
		
		$email->setSubject($subjectForEmail);
		
		$email->setTemplate('UserNotificationEmail');
		
		$itemNumberBooked['ItemBookedFrom'] = $itemNumberBooked['from'];
		
		$itemNumberBooked['ItemBookedTo'] = $itemNumberBooked['to'];
		
		unset($itemNumberBooked['from']);
		
		unset($itemNumberBooked['to']);
		
		$bookingDetails = new DataObject($itemNumberBooked);
					
		$email->populateTemplate($member);
		$email->populateTemplate($items);
		$email->populateTemplate($bookingDetails);
				
		
		// Send email
		$email->send();

		// Send Custodian Email about booking
		$email = new Email();
		
		// Subject to put in for custodians email 
		$subjectForEmail = "Booking Request from ".$member->FirstName." ".$member->Surname;
	
		$email->setTo($this->CustodianEmailAddress);

		$email->setFrom($this->SendEmailAs);
		
		$email->setSubject($subjectForEmail);
		
		$email->setTemplate('CustodianNotificationEmail');
		
		$email->populateTemplate($member);
		
		$email->populateTemplate($items);
		
		$email->populateTemplate($bookingDetails);
										
		// Send email to custodian
		$email->send();		
		
	}
}
