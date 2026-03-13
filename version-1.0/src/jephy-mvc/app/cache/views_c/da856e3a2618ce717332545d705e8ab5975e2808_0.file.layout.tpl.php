<?php
/* Smarty version 4.5.6, created on 2026-03-04 06:37:40
  from 'C:\xampp\htdocs\jephy\version-1.0\jephy-mvc\app\views\layout.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_69a7c52407f3c4_28099907',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'da856e3a2618ce717332545d705e8ab5975e2808' => 
    array (
      0 => 'C:\\xampp\\htdocs\\jephy\\version-1.0\\jephy-mvc\\app\\views\\layout.tpl',
      1 => 1772593282,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:partials/header.tpl' => 1,
  ),
),false)) {
function content_69a7c52407f3c4_28099907 (Smarty_Internal_Template $_smarty_tpl) {
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
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_42628834869a7c5240338f4_54143599', "page-title");
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
</style>

<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_49147113269a7c524035896_11403945', "styles");
?>


</head>
<body>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_145505245869a7c5240363d2_13186514', "header");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_77235662369a7c524076402_80051792', "home");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_75582245769a7c5240770c0_33070370', "services");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_204931367069a7c524077df8_73083153', "about");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_114173749869a7c524078864_60642678', "team");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_164226279669a7c5240792f1_01284712', "features");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_137338344169a7c524079d07_64283987', "maincta");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_38625443969a7c52407a727_15471563', "footer");
?>


<?php echo '<script'; ?>
 src="/assets/js/utils.js"><?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>

const header = document.querySelectorAll( "header" )[0];
window.addEventListener( "scroll", () => {
	if( window.scrollY > 210 ){
		header.classList.add( "fixed" );
	}else{
		header.classList.remove( "fixed" );
	}
} );

Utils.easeScrolls( ".ease-scroll", "data-target" );

const harmbugger 		= document.querySelector( "#harmbugger" );
const navBarCenterMenu 	= document.querySelectorAll( ".nav-bar-center-menu" )[0];
const ctaColumn 		= document.querySelectorAll( ".cta-column" )[0];

const dropdownBtns 		= document.querySelectorAll( "li.dropdown" );


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
		
<?php echo '</script'; ?>
>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_112903340869a7c52407e350_32600264', "scripts");
?>


</body>

</html><?php }
/* {block "page-title"} */
class Block_42628834869a7c5240338f4_54143599 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'page-title' => 
  array (
    0 => 'Block_42628834869a7c5240338f4_54143599',
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
class Block_49147113269a7c524035896_11403945 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'styles' => 
  array (
    0 => 'Block_49147113269a7c524035896_11403945',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<?php
}
}
/* {/block "styles"} */
/* {block "header"} */
class Block_145505245869a7c5240363d2_13186514 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'header' => 
  array (
    0 => 'Block_145505245869a7c5240363d2_13186514',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

	<?php $_smarty_tpl->_subTemplateRender("file:partials/header.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
/* {/block "header"} */
/* {block "home"} */
class Block_77235662369a7c524076402_80051792 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'home' => 
  array (
    0 => 'Block_77235662369a7c524076402_80051792',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="home-slide">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-9">
				<div style="display: flex; align-items: flex-end; flex-direction: column; width: 100%; height: 490px; ">
					<div style="border: 0px solid #f00; margin: auto 0px;">					
						
						<div>
						
							<div class="home-slide-badges">
								<span class="home-slide-top-badge">
									<span class="badge-bull"></span>
									Accepting New Patients
								</span>
							</div>
							
							<h1 class="home-slide-main-heading">
								Faith-based Wellness & Counselling
							</h1>
							
							<h3 class="home-slide-sub-heading">
								Restore your mind, body and spirit. Nurture your path to healing and wholeness.
							</h3>
							
						</div>
						
						<div class="home-slide-ctas">
							<a href="/book-consultation">Book a Consultation</a>
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
/* {/block "home"} */
/* {block "services"} */
class Block_75582245769a7c5240770c0_33070370 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'services' => 
  array (
    0 => 'Block_75582245769a7c5240770c0_33070370',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="services" class="py-5">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-7">
			
				<div class="sectional-headings centered py-5">
					<h6 class="sectional-title-tag">OUR SERVICES</h6>
					<h3>Restoring Alignment</h3>
					<p>
						We provide specialized care tailored to your unique season of life. 
						Explore how we can help you find balance and healing.
					</p>
				</div>
				
			</div>
		</div>
		<div class="row">
		
			<div class="col-md-4">
				<div class="service-column white">
					
					<div class="mb-3">
						<div class="services-icon">
							<img src="/assets/images/icons/user.svg" />
						</div>
						<h2>Individual Therapy</h2>
						<h6>SPACE TO REFLECT. ROOM TO GROW.</h6>
					</div>

					
					<p>
						A confidential environment to explore anxiety, depression, trauma, and its transitions.
						Work towards emotional regulation and lasting transformation.
					</p>
					
					<ul class="custom-list-image">
						<li>Tools for emotional resillence</li>
						<li>Granular self-assurance</li>
						<li>Renewed sense of purpose</li>
					</ul>
					
					<div class="cta-btn-wrapper">
						<div>
							<a href="/therapies/individual-therapy">
								Learn More
							</a>
						</div>						
					</div>
					
					
					
				</div>
			</div>
			
			<div class="col-md-4">
				<div class="service-column dark-blue">
				
					<div class="mb-3">
						<div class="services-icon">
							<img src="/assets/images/icons/users.svg" />
						</div>
						<h2>Couples Conselling</h2>
						<h6>STRENGTHENING CONNECTION.</h6>
					</div>
					
					
					<p>
						Support for partners in rebuilding trust.
						Improving communication, resulving conflict, and deepening 
						emotional and spiritual initimacy.
					</p>
					
					<ul class="custom-list-image">
						<li>Navigate conflict with clarity</li>
						<li>Rebuild connection after hurt</li>
						<li>Develop shared vision</li>
					</ul>
					
					<div class="cta-btn-wrapper dark-blue">
						<div>
							<a href="/therapies/couples-therapy">
								Learn More
							</a>
						</div>						
					</div>
					
					
				</div>
			</div>
						
			<div class="col-md-4">
				<div class="service-column white">				
					
					<div class="mb-3">
						<div class="services-icon">
							<img src="/assets/images/icons/family.svg" />
						</div>
						<h2>Family Therapy</h2>
						<h6>HEALING WITHIN HOME.</h6>
					</div>
					
					<p>
						A structured setting to address conflict, parenting challenges, 
						communication breakdowns, and generation patterns.
					</p>
					
					<ul class="custom-list-image">
						<li>Healthier communication</li>
						<li>Parenting support and guidance</li>
						<li>Restoration of trust</li>
					</ul>
					
					<div class="cta-btn-wrapper">
						<div>
							<a href="/therapies/family-therapy">
								Learn More
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
/* {/block "services"} */
/* {block "about"} */
class Block_204931367069a7c524077df8_73083153 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'about' => 
  array (
    0 => 'Block_204931367069a7c524077df8_73083153',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="about">
	<div class="container">
		<div class="row align-items-center">
			
			<div class="col-md-6 mb-5">
				<img src="/assets/images/img1.jpg" class="w-100 border-radius-14" />
			</div>
			
			<div class="col-md-6">
				<div>
					<h1>
						About <br>TRCWellness.
					</h1>
					<h6 class="sectional-title-tag">
						Healing begins with alignment.
					</h6>
					<div class="py-3"></div>
					<p>
						TRCWellness is a faith-based, science-supported therapeutic clinic 
						serving individuals, couples, and families seeking wholeness. 
						We integrate clinical excellence with spiritual insight to support 
						healing of the mind, heart, and relationships.
					</p>
					<p>
						Our work is grounded in the belief that lasting transformation happens 
						when spirit, soul, and body are brought back into healthy alignment. 
						Through thoughtful care, evidence-based practices, and compassionate 
						presence, we walk with our clients toward clarity, restoration, and 
						renewed strength.
					</p>
					<p>
						We are committed to creating a space that feels safe, dignified, and 
						deeply human — where questions are welcome, growth is intentional, and 
						healing is holistic.
					</p>
					
				</div>
			</div>
			
		</div>

		
		
	</div>
</section>
<?php
}
}
/* {/block "about"} */
/* {block "team"} */
class Block_114173749869a7c524078864_60642678 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'team' => 
  array (
    0 => 'Block_114173749869a7c524078864_60642678',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>


<section id="team">

	<div class="container">
		<div class="row justify-content-left">
			<div class="col-md-12">
			
				<div class="sectional-headings py-5">
					<h6 class="sectional-title-tag">OUR TEAM</h6>
					<h3>Meet the Therapists</h3>
					<p>
						Grounded. Compassionate. Clinically Compasionate. Each session is tailored to
						you by licensed professionals.
					</p>
				</div>
				
			</div>
		</div>
	</div>
	
	<div class="container">
		<div class="row">
		
			<div class="col-md-6 mb-4">
				<div class="team-card">
					<div class="sherilynn-asuoha"></div>
					<div>
						<h2>
							Sherilynn Asuoha
						</h2>
						<div class="team-title">
						
							<h6>
								EdD,
								LCPC
							</h6>
							<span></span>
							<h6>
								Therapist
							</h6>
							
						</div>
						
						<div>
							<p>							
								Licensed Clinical Professional Counsellor with extensive 
								experience serving individuals across diverse backgrounds 
								and life ...					
							</p>
						</div>
						
						<div class="team-tags">
							<small>Trauma</small>
							<small>Anxiety</small>
							<small>Family</small>
						</div>
						
						<div class="team-cta">
							<a class="more-link" href="/team/sherilynn-asuoha">
								View Profile 
								<img src="/assets/images/icons/arrow-right.svg" style="width: 21px;" />
							</a>
						</div>
						
					</div>
				</div>
			</div>
			
			<div class="col-md-6 mb-4">
				<div class="team-card">
					<div class="pavielle-randolph"></div>
					<div>
						<h2>
							Pavielle Randolph
						</h2>
						<div class="team-title">
						
							<h6>
								MSW, LSW
							</h6>
							<span></span>
							<h6>
								Therapist
							</h6>
							
						</div>
						
						<div>
							<p>								
								Licensed Social Worker and trauma‑informed therapist who 
								provides youth centered clinical care focused on ...							
							</p>
						</div>
						
						
						<div class="team-tags">
							<small>Youth</small>
							<small>Emotional Wellness</small>
							<small>Coping Skills</small>
						</div>
						
						<div class="team-cta">
							<a class="more-link" href="/team/pavielle-randolph">
								View Profile 
								<img src="/assets/images/icons/arrow-right.svg" style="width: 21px;" />
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
/* {/block "team"} */
/* {block "features"} */
class Block_164226279669a7c5240792f1_01284712 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'features' => 
  array (
    0 => 'Block_164226279669a7c5240792f1_01284712',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="features">
	<div class="container">
		<div class="row align-items-center">
			
			<div class="col-md-6 mb-5">
				<img src="/assets/images/img2.jpg" class="w-100 border-radius-14" />
			</div>
			
			<div class="col-md-6">
				<div>
					<h1>
						Faith-Based. <br>Clinically Grounded.
					</h1>
					
					<p>
						TRCWellness integrates spiritual insight with 
						evidence-based therapeutic practices. We horo 
						each client faith journey while maintaining the 
						heighest standards of clinical care.
					</p>
					
					<div class="p-3"></div>
					<div class="feature-row">
						<div class="icon">
							<img src="/assets/images/icons/brain.svg" />
						</div>
						<div>
							<h5>Science-Supported</h5>
							<p>
								Evidence based practices grounded in clinical excellence and ethical standards
							</p>
						</div>
					</div>
					
					
					<div class="feature-row">
						<div class="icon">
							<img src="/assets/images/icons/praying-hands.svg" />
						</div>
						<div>
							<h5>Faith-Integrated</h5>
							<p>
								Participation in spiritual components is always guided by consent 
								and appropriateness.
							</p>
						</div>
					</div>
					
					
					<div class="feature-row">
						<div class="icon">
							<img src="/assets/images/icons/diamond.svg" />
						</div>
						<div>
							<h5>Whole-Person Care</h5>
							<p>
								Restoring alignment within the mind, body, and spirit for lasting
								transformation
							</p>
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
/* {/block "features"} */
/* {block "maincta"} */
class Block_137338344169a7c524079d07_64283987 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'maincta' => 
  array (
    0 => 'Block_137338344169a7c524079d07_64283987',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section class="main-cta">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-9">
				<div style="">
					<div style="border: 0px solid #f00; margin: auto 0px;">					
						
						<div class="my-5">
						
							<div class="home-slide-badges">
								<span class="home-slide-top-badge">
									<span class="badge-bull"></span>
									Healing begins with one step.
								</span>
							</div>
							
							<h2 class="home-slide-main-heading">
								Begin Your Journey
							</h2>
							
							<h3 class="home-slide-sub-heading">
								Schedule a consultation to learn how we can support you.
							</h3>
							
						</div>
						
						<div class="home-slide-ctas">
							<a href="/book-consultation">Book a Consultation</a>
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
class Block_38625443969a7c52407a727_15471563 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'footer' => 
  array (
    0 => 'Block_38625443969a7c52407a727_15471563',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="footer">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-md-7">
				<div class="footer-form-content">
					<h1>
						<span class="white-color">Healing begins with </span><br>
						<span class="pale-color italic">one step</span>
					</h1>
					
					<div class="my-5">
						<p class="white-texts">
							Schedule a consultation to learn how we can support you.
							We are committed to create a space that feels safe, dignified,
							and deeply human.							
						</p>
					</div>
					
					<div class="footer-form-ctas">
						<div>
							<a class="book-consultation">Book Free Consultation</a>
						</div>
						<div>
							<a class="verify-issuance">Verify Insurance</a>
						</div>					
					</div>
					
					<div class="footer-form-contact-info">
						<div>
							<small>EMAIL US</small><br>
							<a href="mailto:<?php echo $_smarty_tpl->tpl_vars['site']->value['email'];?>
"><?php echo $_smarty_tpl->tpl_vars['site']->value['email'];?>
</a>
						</div>
						<div>
							<small>CALL US</small><br>
							<a href="tel:<?php echo $_smarty_tpl->tpl_vars['site']->value['phone'];?>
"><?php echo $_smarty_tpl->tpl_vars['site']->value['phone'];?>
</a>
						</div>					
					</div>
					
				</div>
			</div>
			<div class="col-md-5 col-sm-12">
				<div class="footer-contact-form">
					<h4>
						Send us a message
					</h4>
					<hr />
					<form>
						
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<small>FIRST NAME</small>
									<input type="text" name="firstname" class="form-control" placeholder="Enter First Name" />
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<small>LAST NAME</small>
									<input type="text" name="lastname" class="form-control" placeholder="Enter Last Name" />
								</div>
							</div>							
						</div>
						
						<div class="form-group">
							<small>EMAIL</small>
							<input type="email" name="email" class="form-control" placeholder="Enter Email Address" />
						</div>
						
						<div class="form-group">
							<small>SERVICE INTEREST</small>
							<select class="form-control" name="service-type">
								<option value="">Select one</option>
								<option value="Individual Therapy">Individual Therapy</option>
								<option value="Couples Therapy">Couples Therapy</option>
								<option value="Family Therapy">Family Therapy</option>
							</select>
						</div>
						
						
						<div class="form-group">
							<small>MESSAGE</small>
							<textarea class="form-control" rows="5" name="message" placeholder="Compose your message ..."></textarea>
						</div>
						
						<div class="form-group">
							<button class="btn footer-contact-form-btn">Send Message</button>
						</div>
						
						
					</form>
				</div>
			</div>
		</div>
	</div>

	<footer class="pt-5">
		<div class="container grey-border-top">
			<div class="pt-4"></div>
			<div class="row">
				<div class="col-md-4 col-sm-12 mb-5">
					<div class="mr-5">
						<div class="logo-wrapper mb-3">
							<a href="/">
								<img src="/assets/images/logo.png" class="nav-bar-logo" />
							</a>
						</div>
						<p class="grey-texts">
							Reforming alignment within the mind, body, and spirit through faith-based,
							science support therapy.
						</p>
					</div>
				</div>
				
				<div class="col-md-3 col-sm-12 mb-5">
					<div>
						<h6 class="white-texts">Services</h6>
						<ul class="footer-list">
							<li><a href="/therapies/individual-therapy">Individual Therapy</a></li>
							<li><a href="/therapies/couples-therapy">Couples Therapy</a></li>
							<li><a href="/therapies/family-therapy">Family Therapy</a></li>							
							
						</ul>
					</div>
				</div>
				
				<div class="col-md-2 col-sm-12 mb-5">
					<div>
						<h6 class="white-texts">Company</h6>
						<ul class="footer-list">
							<li><a class="" href="/about-us">About Us</a></li>
							<li><a class="ease-scroll" data-target="team" href="/">Our Team</a></li>						
							<li><a class="ease-scroll" data-target="footer" href="/">Contact</a></li>
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
						<a href="<?php echo $_smarty_tpl->tpl_vars['site']->value['url']['home'];?>
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
class Block_112903340869a7c52407e350_32600264 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'scripts' => 
  array (
    0 => 'Block_112903340869a7c52407e350_32600264',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<?php
}
}
/* {/block "scripts"} */
}
