<div class="ItemDisplay">				
	$Content
	<% loop items %>		
		<div class="ItemWrapper">
			<div class="middle">			
				<div class="ItemTitle">
					<h1>$Item</h1>				
				</div> <!-- .itemTitle-->
			</div>
			
			<div class="middle">
				<div class="container">			
					<div class="ItemDescription">
						<p>$Description.LimitWordCount(20)</p>
					</div><!-- .Description-->							
				</div><!-- .container-->

				<div class="ItemPhoto">
					<a href=$itemPhoto.Link data-lightbox="$Item" data-title="$Item">$itemPhoto.setWidth(150)</a>
				</div><!-- .ItemPhoto-->
				<div class="ItemBook">
					<a href="$Link">Book Me!</a>
				</div> <!-- .itemBook -->										
			</div><!-- .middle-->			
		</div><!-- .ItemWrapper -->
	<% end_loop %>	  
</div>	