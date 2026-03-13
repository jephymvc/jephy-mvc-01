<?php
/* Smarty version 4.5.6, created on 2026-03-09 15:55:31
  from 'C:\xampp\htdocs\jephy\version-1.0\src\jephy-mvc\app\views\partials\header.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_69aedf63e787c9_36077267',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '96151c3220e97713239635d2a32c9dfa7734d154' => 
    array (
      0 => 'C:\\xampp\\htdocs\\jephy\\version-1.0\\src\\jephy-mvc\\app\\views\\partials\\header.tpl',
      1 => 1772320978,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69aedf63e787c9_36077267 (Smarty_Internal_Template $_smarty_tpl) {
?><header>
	<div class="container">
		<nav class="nav-bar">
			<div class="logo-wrapper">
				<a href="/">
					<img src="/assets/images/logo.png" class="nav-bar-logo" />
				</a>
				<span id="harmbugger">
					<span></span>
					<span></span>
					<span></span>
					<span></span>
				</span>
			</div>
			<div class="nav-bar-center-menu">
				<ul>
					<li><a href="/">Home</a></li>
					<li><a href="/about-us">About</a></li>
					<!--
						<li class="dropdown">
							<a>Therapy</a>
							<ul>
								<li><a href="">Personal Therapy</a></li>
								<li><a href="">Couple Therapy</a></li>
								<li><a href="">Family Therapy</a></li>						
							</ul>
						</li>
						<li><a href="/">FAQ</a></li> 
					-->
					<li><a href="/therapies">Therapies</a></li>
					<li><a href="/" class="ease-scroll" data-target="team">Team</a></li>
					<li><a href="/" class="ease-scroll" data-target="footer">Contact</a></li>
					
				</ul>
			</div>
			
			<div class="cta-column">
				<ul>
					<li>
						<a class="nav-bar-book-appointment-cta" href="/book-consultation">
							Book Consultation
						</a>
					</li>
				</ul>
			</div>
		</nav>
	</div>
</header>
<?php }
}
