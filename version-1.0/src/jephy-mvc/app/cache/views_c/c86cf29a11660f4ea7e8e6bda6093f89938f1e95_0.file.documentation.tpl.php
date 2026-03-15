<?php
/* Smarty version 4.5.6, created on 2026-03-15 13:41:13
  from 'C:\xampp\htdocs\jephy\version-1.0\src\jephy-mvc\app\views\home\documentation.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_69b6a8e9d921c6_40995904',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c86cf29a11660f4ea7e8e6bda6093f89938f1e95' => 
    array (
      0 => 'C:\\xampp\\htdocs\\jephy\\version-1.0\\src\\jephy-mvc\\app\\views\\home\\documentation.tpl',
      1 => 1773577370,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:partials/header.tpl' => 1,
  ),
),false)) {
function content_69b6a8e9d921c6_40995904 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_162869623469b6a8e9d8b1e5_90338030', "styles");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_207721550169b6a8e9d8e1a8_02973688', "header");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_93234576169b6a8e9d91914_83911138', "home");
$_smarty_tpl->inheritance->endChild($_smarty_tpl, "layout.tpl");
}
/* {block "styles"} */
class Block_162869623469b6a8e9d8b1e5_90338030 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'styles' => 
  array (
    0 => 'Block_162869623469b6a8e9d8b1e5_90338030',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<style>
	#home-slide {
		height: 280px !important;
		min-height: 280px !important;		
	}
</style>
<?php
}
}
/* {/block "styles"} */
/* {block "header"} */
class Block_207721550169b6a8e9d8e1a8_02973688 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'header' => 
  array (
    0 => 'Block_207721550169b6a8e9d8e1a8_02973688',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

	<?php $_smarty_tpl->_subTemplateRender("file:partials/header.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
/* {/block "header"} */
/* {block "home"} */
class Block_93234576169b6a8e9d91914_83911138 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'home' => 
  array (
    0 => 'Block_93234576169b6a8e9d91914_83911138',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="home-slide">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-md-10">
				<div style="display: flex; align-items: flex-end; flex-direction: column; width: 100%; height: 280px; ">
					<div style="border: 0px solid #f00; margin: auto 0px;">					
						
						<div>
													
							
							
							<h1 class="home-slide-main-heading centered-texts">
								Documentation
							</h1>
							
							<h3 class="home-slide-sub-heading centered-texts">
								The modern PHP framework that provides elegant syntax, 
								robust tools, and a delightful developer experience.
								
								Get started in minutes.
							</h3>
							
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
}
