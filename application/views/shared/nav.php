<nav class="navbar navbar-default navbar-inverse" role="navigation">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-01">
			<span class="sr-only">Toggle navigation</span>
		</button>
		<a class="navbar-brand" href="/"><img src="/images/logo-small.png" alt="Statiq"></a>
	</div>
	<div class="collapse navbar-collapse" id="navbar-collapse-01">
		<ul class="nav navbar-nav">			      
			<li <?php if( $page == "home" ):?>class="active"<?php endif?>><a href="/">Home</a></li>
			<li><a href="/about">About</a></li>
			<li><a href="/pricing">Pricing</a></li>
      		<li><a href="/contact">Contact</a></li>	
		</ul> 
		<ul class="nav navbar-nav navbar-right">			      
			<li><a href="/login">Login</a></li>	
		</ul> 		      
	</div><!-- /.navbar-collapse -->
</nav><!-- /navbar -->