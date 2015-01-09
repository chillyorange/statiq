<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Statiq - working...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Loading Bootstrap -->
    <link href="/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Loading Flat UI -->
    <link href="/css/flat-ui.css" rel="stylesheet">
    
    <!-- Loading custom css -->
    <link href="/css/custom.css" rel="stylesheet">
    
    <!-- Loading loader css -->
    <link href="/css/loader.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
      <script src="/js/html5shiv.js"></script>
      <script src="/js/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  
    <div class="container">
    	
    	<div class="row">
    	
    		<?php $this->load->view("shared/nav");?>
    	
    		<div class="col-md-10 col-md-offset-1" id="content">
    			
    			<div id="loading">
    			
    				<br>
    			
    				<img src="/images/loader.gif" alt="Loading..." class="loading">
    			
    				<br>
    			
    				<p class="text-center tag">
    				This might take a minute depending on the size of your site, sit tight...
    				</p>
    			
    			</div><!-- /#loading -->
    			
    		</div><!-- /.col-md-10 -->
    		
    	</div><!-- /.row -->
    	
    </div><!-- /.container -->


    <!-- Load JS here for greater good =============================-->
    <script src="/js/jquery-1.8.3.min.js"></script>
    <script src="/js/jquery-ui-1.10.3.custom.min.js"></script>
    <script src="/js/jquery.ui.touch-punch.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/bootstrap-select.js"></script>
    <script src="/js/bootstrap-switch.js"></script>
    <script src="/js/flatui-checkbox.js"></script>
    <script src="/js/flatui-radio.js"></script>
    <script src="/js/jquery.tagsinput.js"></script>
    <script src="/js/jquery.placeholder.js"></script>
    <script src="/js/application.js"></script>
    <script>
    $(function(){
    
    	/*$.ajax({
    		type: "POST",
    		dataType: "json",
    	  	url: "/statiq/startCrawl/<?php echo $siteID?>"
    	}).done(function(data){
    	
    		$('#loading').hide();
    		
    		$('#content').append( $(data.content) )
    	
    	})*/
    
    })
    </script>
  </body>
</html>
