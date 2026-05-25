<?php
/* Smarty version 4.5.6, created on 2026-04-27 21:29:19
  from 'C:\xampp\htdocs\jephy\version-1.0\src\jephy-mvc\app\views\partials\header.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_69efb90f591eb9_68158376',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '96151c3220e97713239635d2a32c9dfa7734d154' => 
    array (
      0 => 'C:\\xampp\\htdocs\\jephy\\version-1.0\\src\\jephy-mvc\\app\\views\\partials\\header.tpl',
      1 => 1773644997,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_69efb90f591eb9_68158376 (Smarty_Internal_Template $_smarty_tpl) {
?><header>
	<div class="container">
		<nav class="nav-bar">
			<div class="logo-wrapper">
				<a href="/home/">
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
					<li><a href="/getting-started">Getting Started</a></li>
					<li><a href="/api-references">API Reference</a></li>					
					<li><a href="/components">Components</a></li>
					<li><a href="/blog">Blog</a></li>
					
				</ul>
			</div>
			
			<div class="cta-column">
				<ul>
					<li>
						<a class="nav-bar-book-appointment-cta" target="_blank" href="<?php echo $_smarty_tpl->tpl_vars['site']->value['url']['third_party_platform']['github'];?>
">
							<img src="/assets/icons/download-white.svg" style="width: 21px;" />
							Download ZIP
						</a>
					</li>
				</ul>
			</div>
		</nav>
	</div>
</header>
<?php }
}
