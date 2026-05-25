<?php
/* Smarty version 4.5.6, created on 2026-04-27 21:29:18
  from 'C:\xampp\htdocs\jephy\version-1.0\src\jephy-mvc\app\views\home\documentation.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.6',
  'unifunc' => 'content_69efb90e4b2d57_73826984',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c86cf29a11660f4ea7e8e6bda6093f89938f1e95' => 
    array (
      0 => 'C:\\xampp\\htdocs\\jephy\\version-1.0\\src\\jephy-mvc\\app\\views\\home\\documentation.tpl',
      1 => 1777063018,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:partials/header.tpl' => 1,
  ),
),false)) {
function content_69efb90e4b2d57_73826984 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, true);
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_157849525069efb90e41f641_69524502', "styles");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_136043768169efb90e4230d3_75985477', "header");
?>


<?php 
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_189791203769efb90e4b1127_21581396', "home");
$_smarty_tpl->inheritance->endChild($_smarty_tpl, "layout.tpl");
}
/* {block "styles"} */
class Block_157849525069efb90e41f641_69524502 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'styles' => 
  array (
    0 => 'Block_157849525069efb90e41f641_69524502',
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
class Block_136043768169efb90e4230d3_75985477 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'header' => 
  array (
    0 => 'Block_136043768169efb90e4230d3_75985477',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

	<?php $_smarty_tpl->_subTemplateRender("file:partials/header.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
/* {/block "header"} */
/* {block "home"} */
class Block_189791203769efb90e4b1127_21581396 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'home' => 
  array (
    0 => 'Block_189791203769efb90e4b1127_21581396',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<section id="home-slide">
	<div class="container">
		<div class="row align-items-center justify-content-center">
			<div class="col-md-9 col-sm-12">
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
