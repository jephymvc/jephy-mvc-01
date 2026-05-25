<?php
/* Smarty version 4.5.6, created on 2026-04-27 21:47:37
  from 'C:\xampp\htdocs\jephy\version-1.0\src\jephy-mvc\app\views\layout.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_69efbd597b5326_15431118',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '67446387bd758c325b3ccf7e95fbf6c38584bbf1' => 
    array (
      0 => 'C:\\xampp\\htdocs\\jephy\\version-1.0\\src\\jephy-mvc\\app\\views\\layout.tpl',
      1 => 1777138647,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69efbd597b5326_15431118 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
?>
<!DOCTYPE html>
<html lang="en-US" dir="ltr">
<head>
<base href="/" />
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">	
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<meta property="og:title" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['title'];?>
">
<meta property="og:description" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['description'];?>
">
<meta property="og:image:url" content="/open-graph-img.jpg"/>
<meta property="og:image:type" content="image/jpeg" />
<meta property="og:image:width" content="600" />										 
<meta property="og:site_name" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['name'];?>
" />


<meta name="twitter:card" content="summary"/>
<meta name="twitter:description" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['description'];?>
"/>
<meta name="twitter:title" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['title'];?>
"/>
<meta name="twitter:domain" content="/" />

 
<meta name="author" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['author']['fullname'];?>
">    
<meta name="keywords" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['keywords'];?>
">
<meta property="keywords" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['keywords'];?>
">        
<meta name="description" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['description'];?>
">
<meta property="description" content="<?php echo $_smarty_tpl->tpl_vars['site']->value['description'];?>
">

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_15521608169efbd597a1419_36118917', "page-title");
?>

  
<link rel="shortcut icon" type="image/png" href="/favicon.png">
<meta name="theme-color" content="#ffffff">
<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
<link href="/assets/css/style.css" rel="stylesheet" />

<style>

ul.custom-list-image {
	list-style: none;
	padding-left: 0;
	margin: 5px;
}

ul.custom-list-image li {
	position: relative;
	padding-left: 25px; /* space for bullet */
}

ul.custom-list-image > li{
	list-style-type: none;
}

ul.custom-list-image > li::before {
	content: "";
	position: absolute;
	left: -6px;
	top: 0px;
	width: 21px;          /* 👈 control size here */
	height: 21px;
	background-image: url("/assets/images/icons/check-square.svg");
	background-size: contain;
	background-repeat: no-repeat;
}

.border-radius-1{
	border-radius: 7px;
}

.border-radius-2{
	border-radius: 14px;
}

.border-radius-3{
	border-radius: 21px;
}

.home-slide-cta-btns{
	display: flex;
	justify-content: flex-start;
	gap: 21px;
	margin-top: 49px;
}

.home-slide-cta-btns > a{
	display: block;
	background-color: #fff;
	padding: 10px 21px;
	text-decoration: none;
	border-radius: 28px;
	font-weight: bold;
	color: #444;
}

.home-slide-cta-btns > a.blue-bg{
	background-color: #000000 !important;
	color: #ffffff !important;
}


.home-slide-cta-btns > a > span{
	font-size: 14px;
}

.bold-900{
	font-weight: 900;
}

.home-slide-content{
	display: flex; 
	align-items: flex-end; 
	flex-direction: column; 
	width: 100%; 
	height: 490px;
}

.box-shadow{
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 7px 21px rgba(0, 0, 0, 0.06);
}

@media (max-width: 768px) {

	#home-slide{
		min-height: 100vh;
		padding-top: 2rem;
	}
	
	.nav-bar {
        padding: 0px 0 !important;
    }

	.nav-bar-logo {
		width: 120px;
		padding: 10px 0;
	}
	
	.home-slide-cta-btns > a {
		display: block;
		background-color: #fff;
		padding: 10px 14px;
		text-decoration: none;
		border-radius: 28px;
		font-weight: bold;
		color: #444;
		font-size: 14px;
	}
	
	#footer-cta{
		padding-bottom: 3rem;
	}
	
	.home-slide-content{
		display: flex; 
		align-items: flex-start; 
		justify-content: flex-start; 
		flex-direction: column; 
		width: 100%; 
		min-height: 70vh !important;
		
	}
	
	.home-slide-main-heading{
		font-size: 2.5rem !important;
	}
	
	.section-heading{
		font-size: 2.5rem !important;
		margin-bottom: 1rem;
	}
	
	.home-slide-sub-heading{
		line-height: 28px;
	}

	.home-slide-cta-btns{
		display: block !important;
		margin-bottom: 1rem !important;
	}

	.home-slide-cta-btns > a{
		margin-bottom: 2rem;
		text-align: center !important; 
	}

	.service-column {
		min-height: 280px !important; 	
	}
	
}



</style>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_129649791369efbd597a59a5_90644065', "styles");
?>


</head>
<body>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_55226802869efbd597a6a14_97073336', "header");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_200143124069efbd597a79e9_52509023', "home");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_108842285169efbd597a97c0_25759505', "services");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_75145276469efbd597aae13_66465083', "maincta");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_12176621569efbd597acd57_39156650', "footer");
?>


<?php echo '<script'; ?>
 src="/assets/js/utils.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>

const header = document.querySelectorAll( "header" )[0];
if( document.body.contains( header ) ){
	window.addEventListener( "scroll", () => {
		if( window.scrollY > 210 ){
			header.classList.add( "fixed" );
		}else{
			header.classList.remove( "fixed" );
		}
	} );
}

Utils.easeScrolls( ".ease-scroll", "data-target" );

const harmbugger 		= document.querySelector( "#harmbugger" );
const navBarCenterMenu 	= document.querySelectorAll( ".nav-bar-center-menu" )[0];
const ctaColumn 		= document.querySelectorAll( ".cta-column" )[0];

const dropdownBtns 		= document.querySelectorAll( "li.dropdown" );



if( document.body.contains( harmbugger ) ){
	harmbugger.addEventListener( "click", ( evt ) => {
		evt.preventDefault();
		
		if( !navBarCenterMenu.classList.contains( "show" ) ){
			navBarCenterMenu.classList.add( "show" );
		}else{
			navBarCenterMenu.classList.remove( "show" );
		}
			
		if( !ctaColumn.classList.contains( "show" ) ){
			ctaColumn.classList.add( "show" );
		}else{
			ctaColumn.classList.remove( "show" );
		}	
		
	} );
}


if( document.body.contains( dropdownBtns[0] ) ){
	[...dropdownBtns].forEach( ( btn ) => {
		btn.addEventListener( "click", ( evt ) => {
			evt.preventDefault();
			const dropmenu = btn.querySelectorAll( 'ul' )[0];
			if( dropmenu.classList.contains( 'dropmenu' ) ){
				dropmenu.classList.remove( 'show' );
			}else{
				dropmenu.classList.add( 'show' );
			}
		} );
	} );
}

		
<?php echo '</script'; ?>
>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_158414440169efbd597b2fb9_23418478', "scripts");
?>


</body>

</html><?php }
/* {block "page-title"} */
class Block_15521608169efbd597a1419_36118917 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'page-title' => 
  array (
    0 => 'Block_15521608169efbd597a1419_36118917',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

	<title>
		<?php echo $_smarty_tpl->tpl_vars['site']->value['title'];?>

	</title>
<?php
}
}
/* {/block "page-title"} */
/* {block "styles"} */
class Block_129649791369efbd597a59a5_90644065 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'styles' => 
  array (
    0 => 'Block_129649791369efbd597a59a5_90644065',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<?php
}
}
/* {/block "styles"} */
/* {block "header"} */
class Block_55226802869efbd597a6a14_97073336 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'header' => 
  array (
    0 => 'Block_55226802869efbd597a6a14_97073336',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

	<?php
}
}
/* {/block "header"} */
/* {block "home"} */
class Block_200143124069efbd597a79e9_52509023 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'home' => 
  array (
    0 => 'Block_200143124069efbd597a79e9_52509023',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="home-slide">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-md-6">
				<div class="home-slide-content">
					<div style="border: 0px solid #f00;">					
						
						<div>
						
							
							<a href="/home">
								<img src="/assets/images/logo-white.svg" class="nav-bar-logo" />
							</a>
							
							<br />
							<br />
							
							<div class="home-slide-badges">
								<span class="home-slide-top-badge">
									<span class="badge-bull"></span>
									Light Weight and Host Platform Agnostic
								</span>
							</div>
							
							<h1 class="home-slide-main-heading">
								Build PHP Apps <br />Faster & Cleaner
							</h1>
							
							<h3 class="home-slide-sub-heading">
								The modern PHP framework that provides elegant syntax, 
								robust tools, and a delightful developer experience.								
								Get started in minutes.
							</h3>
							
						</div>
						
						<div class="home-slide-cta-btns">
							<a class="blue-bg" target="_blank" href="<?php echo $_smarty_tpl->tpl_vars['site']->value['url']['third_party_platform']['github'];?>
">
								<img src="/assets/icons/download-white.svg" style="width: 21px;" />
								<span>Download ZIP</span>
							</a>
							<a href="/documentation">
								<span>Read Documentation</span>
								<img src="/assets/icons/arrow-right.svg" style="width: 21px;" />
							</a>							
						</div>
						
					</div>
				</div>
				
			</div>
			
			<div class="col-md-6">
				<div class="py-5 box-shadow">
					<img src="/assets/images/code-sample.jpg" class="w-100 border-radius-3" />
				</div>
			</div>
			
		</div>
	</div>
</section>

<?php
}
}
/* {/block "home"} */
/* {block "services"} */
class Block_108842285169efbd597a97c0_25759505 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'services' => 
  array (
    0 => 'Block_108842285169efbd597a97c0_25759505',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="services" class="py-5">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-9">
			
				<div class="sectional-headings centered py-5">
					<h3 class="section-heading">
						Everything you need to build robust applications
					</h3>
					<p>
						From simple monolith and RESTful APIs to complex enterprise and 
						distributed/containerized applications, we provide the tools 
						you need to succeed.
					</p>
				</div>
				
			</div>
		</div>
		<div class="row">
		
			<div class="col-md-3">
				<div class="service-column white">
					
					<div class="mb-3">
						<div class="services-icon">
							<img src="/assets/icons/lightning.svg" />
						</div>
						<h2>Lightning Fast</h2>
						<h6>FOR SPEED & MINIMAL OVERHEAD</h6>
					</div>

					
					<p>
						A lightweight, high-performance PHP MVC framework designed for 
						speed and minimal overhead. It is built to be "obsessed with 
						speed" by loading only the bare essentials.
					</p>
				
										
					
				</div>
			</div>
			
			<div class="col-md-3">
				<div class="service-column dark-blue">
				
					<div class="mb-3">
						<div class="services-icon">
							<img src="/assets/icons/security-white.svg" />
						</div>
						<h2>Secured by Default</h2>
						<h6>ENTERPRISE-GRADE SECURITY.</h6>
					</div>
					
					
					<p>
						A high-performance PHP framework offering extreme speed and 
						"Secured by Default" features, allowing developers to prioritize 
						building over infrastructure hardening.
					</p>
										
				</div>
			</div>
						
			<div class="col-md-3">
				<div class="service-column white">				
					
					<div class="mb-3">
						<div class="services-icon">
							<img src="/assets/icons/database.svg" />
						</div>
						<h2>Elegant ORM</h2>
						<h6>INTUITIVE & DEVELOPER FRIENDLY</h6>
					</div>
					
					<p>
						An intuitive, developer-friendly database mapper that simplifies 
						complex queries into readable code, boosting productivity through 
						various refined, expressive syntax and GraphQL support.
					</p>
					
									</div>
			</div>
									
			<div class="col-md-3">
				<div class="service-column white">				
					
					<div class="mb-3">
						<div class="services-icon">
							<img src="/assets/icons/cloud.svg" />
						</div>
						<h2>Hosting Agnostic</h2>
						<h6>FLEXIBLE AND OPERATES SEAMLESSLY</h6>
					</div>
					
					<p>
						It's flexible and operates seamlessly across any server 
						environment, ensuring consistent performance whether 
						deployed on-premise, Docker, or any cloud platform.
					</p>					
					
										
				</div>
			</div>
			
			
		</div>
	</div>
</section>
<?php
}
}
/* {/block "services"} */
/* {block "maincta"} */
class Block_75145276469efbd597aae13_66465083 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'maincta' => 
  array (
    0 => 'Block_75145276469efbd597aae13_66465083',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="footer-cta" class="main-cta">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-12">
				<div style="">
					<div style="border: 0px solid #f00; margin: auto 0px;">					
						
						<div class="my-5">
						
							<div class="home-slide-badges">
								<span class="home-slide-top-badge">
									<span class="badge-bull"></span>
									Big dream begins with one step.
								</span>
							</div>
							
							<h2 class="home-slide-main-heading">
								Ready to start building?
							</h2>
							
							<h3 class="home-slide-sub-heading">
								Download the latest release package. It includes 
								everything you need to get your project up and running quickly.
							</h3>
							
						</div>
						
						
						<div class="home-slide-cta-btns">
							<a class="blue-bg" target="_blank" href="<?php echo $_smarty_tpl->tpl_vars['site']->value['url']['third_party_platform']['github'];?>
">
								<img src="/assets/icons/download-white.svg" style="width: 21px;" />
								<span>Download ZIP</span>
							</a>
							<a href="/documentation">
								<span>Read Documentation</span>
								<img src="/assets/icons/arrow-right.svg" style="width: 21px;" />
							</a>							
						</div>
						
					</div>
				</div>
				
			</div>
			
		</div>
	</div>
</section>
<?php
}
}
/* {/block "maincta"} */
/* {block "footer"} */
class Block_12176621569efbd597acd57_39156650 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'footer' => 
  array (
    0 => 'Block_12176621569efbd597acd57_39156650',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="footer">
	
	<footer class="pt-2">
		<div class="container">
			<div class="pt-4"></div>
			<div class="row">
				<div class="col-md-4 col-sm-12 mb-5">
					<div class="mr-5">
						<div class="logo-wrapper mb-3">
							<a href="/">
								<img src="/assets/images/logo-white.svg" class="nav-bar-logo" />
							</a>
						</div>
						<p class="grey-texts">
							The framework for web Artisans. Build faster, better, and more elegant applications.
						</p>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-12 mb-5">
					<div>
						<h6 class="white-texts">Documentation</h6>
						<ul class="footer-list">
							<li><a href="/documentation/getting-started">Getting Started</a></li>
							<li><a href="/documentation/routing">Routing</a></li>
							<li><a href="/documentation/controllers">Controllers</a></li>							
							<li><a href="/documentation/database">Database</a></li>							
							<li><a href="/documentation/stitching">Templating/Stitching</a></li>							
						</ul>
					</div>
				</div>
				
				<div class="col-md-2 col-sm-12 mb-5">
					<div>
						<h6 class="white-texts">Resources</h6>
						<ul class="footer-list">
							<li><a class="" href="/about-us">Tutorial</a></li>
							<li><a class="ease-scroll" data-target="team" href="/">Blog</a></li>						
							<li><a class="ease-scroll" data-target="footer" href="/">Packages</a></li>
							<li><a class="ease-scroll" data-target="footer" href="/">API Reference</a></li>
						</ul>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-12 mb-5">
					<div>
						<h6 class="white-texts">Newsletter</h6>
						<p class="grey-texts">Subscribe for wellness tips and updates</p>
					</div>
					<div>
						<form id="footer-newsletter-form">
							<input placeholder="Enter email" />
							<button>	
								<img src="/assets/images/icons/white-arrow-right.svg" style="width: 21px;" />
							</button>
						</form>
					</div>					
				</div>
				
			</div>
		</div>
		
		<div class="container grey-border-top">
			<div class="row">
				<div class="col-md-12">
					<small>
						<span class="white-texts">&copy; Copyright 2026.</span> 
						<a class="theme-links" href="<?php echo $_smarty_tpl->tpl_vars['site']->value['url']['home'];?>
"><?php echo $_smarty_tpl->tpl_vars['site']->value['name'];?>
</a>
					</small>
				</div>
			</div>
		</div>
	
	</footer>

</section>
<?php
}
}
/* {/block "footer"} */
/* {block "scripts"} */
class Block_158414440169efbd597b2fb9_23418478 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'scripts' => 
  array (
    0 => 'Block_158414440169efbd597b2fb9_23418478',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<?php
}
}
/* {/block "scripts"} */
}
