<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Statiq</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Loading Bootstrap -->
    <link href="/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Loading Flat UI -->
    <link href="/css/flat-ui.css" rel="stylesheet">
    
    <!-- Loading custom css -->
    <link href="/css/custom.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
      <script src="/js/html5shiv.js"></script>
      <script src="/js/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  
    <div class="container">
    
    	<?php $this->load->view("shared/nav");?>
    	
    	<div class="row">
    	
    		<div class="col-md-10 col-md-offset-1">
    			
    			<h1 class="text-center">
    				<img src="/images/logo.png" alt="Statiq">
    			</h1>
    			
    			<p class="text-center tag">
    				Compile your <b>Wordpress</b>, <b>Joomla</b>, <b>Drupal</b> or any other CMS-based site into a lightening fast static HTML site!
    			</p>
    		    		
    		</div><!-- /.col-md-10 -->
    		
    	</div><!-- /.row -->
    	
    	<div class="row">
    	
    		<div class="col-md-8 col-md-offset-2">
    		
    			<?php if( $this->session->flashdata('error') != '' ):?>
    			<div class="alert alert-error">
    				<button type="button" class="close fui-cross" data-dismiss="alert"></button>
    				<h4>Error</h4>
    				<p>
    					<?php echo $this->session->flashdata('error');?>
    				</p>
    			</div>
    			<?php endif;?>
    			
    			<form class="form domainBar" method="post" action="/statiq/">
    				<div class="form-group">
    					<div class="input-group input-group-lg">
    						<span class="input-group-addon">http://</span>
    							<input type="text" placeholder="yourdomain.com" id="domain" name="domain" class="form-control" value="<?php if( $this->session->flashdata('domain') != '' ) { echo $this->session->flashdata('domain'); }?>">
    						<span class="input-group-btn">
    							<button type="submit" class="btn btn-default">
    								Try <b>Statiq</b>!
    							</button>
    						</span>
    					</div>
    				</div>
    			</form>
    		
    		</div><!-- /.col-md-8 -->
    	
    	</div><!-- /.row -->
    	
    	<hr>
    	
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
    
    	$('input#domain').focus();
    
    })
    </script>
  </body>
</html>
