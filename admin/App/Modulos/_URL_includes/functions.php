<?

function template ($id,$slug,$obs, $_token_){
	global $session;
	$retorno= "<li data-id='".$id."' data-token='".$_token_."'>
					<div>
						<div class='w1 titulo'>".$slug."</div>
						<div class='w1 obs'>".$obs."</div>
						<div id='combo'>
							<div id='detalhes_img'>
								<span><img class='editar legenda' 		legenda='Editar' 		 				src='./App/Templates/img/websheep/layer--pencil.png'></span>
								<span><img class='excluir legenda' 		legenda='<img class=\"editar\" 			src=\"/admin/App/Templates/img/websheep/exclamation.png\" style=\"position: absolute;margin-top: -2px;\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Excluir'		src='".$session->get('PATCH')."/img/cross-button.png'></span>
							</div>
						</div>
					</div>
				</li>";
	return $retorno;
}


function SalvaPaths(){
	global $session;
	$inputs= array();
	parse_str($_REQUEST['Formulario'], $inputs);
	$setupdata 					= new MySQL();
	$setupdata->set_table(PREFIX_TABLES.'setupdata');
	$setupdata->set_where('id="1"');
	$setupdata->set_update('url_initPath',	$inputs['url_initPath']);
	$setupdata->set_update('url_setRoot',	$inputs['url_setRoot']);
	$setupdata->set_update('url_set404',	$inputs['url_set404']);
	$setupdata->set_update('url_plugin',	$inputs['url_plugin']);
	if(isset($inputs['url_ignore_add']) && $inputs['url_ignore_add']=="0"){
		$setupdata->set_update('url_ignore_add','1');
	}else{
		$setupdata->set_update('url_ignore_add','0');

	}
	
	if($setupdata->salvar()){

		echo "sucesso";exit;

	};



}
function salvaInclude(){
	global $session;
	$id_include 	=	$_REQUEST['id_include'];
	$file 			=	$_REQUEST['file'];
	$info 			=	$_REQUEST['info'];

	$File_atual 					= new MySQL();
	$File_atual->set_table(PREFIX_TABLES.'ws_pages');
	$File_atual->set_where('id="'.$id_include.'"');
	$File_atual->select();

	$Other 					= new MySQL();
	$Other->set_table(PREFIX_TABLES.'ws_pages');
	$Other->set_where('id<>"'.$id_include.'"');
	$Other->set_where('AND file="'.$file.'"');
	$Other->select();

	$Atual_Igual_Novo 			= ($File_atual->fetch_array[0]['file']==$file) 							? true : false;
	$Se_file_BD_existe 			= (file_exists(ROOT_WEBSITE.'/'.$File_atual->fetch_array[0]['file'])) 	? true : false;
	$Se_novo_file_existe 		= (file_exists(ROOT_WEBSITE.'/'.$file)) 								? true : false;
	$Registro_Em_Outros_Tools 	= $Other->_num_rows >=1;
	$registro_atual_vaziu 		= $File_atual->fetch_array[0]['file']=="";

	if(!$registro_atual_vaziu && !$Se_novo_file_existe && $Se_file_BD_existe && !$Registro_Em_Outros_Tools && !$Atual_Igual_Novo &&    $info=="renomear"){
			if(rename(ROOT_WEBSITE.'/'.$File_atual->fetch_array[0]['file'],ROOT_WEBSITE.'/'.$file)){
				$NewPage 					= new MySQL();
				$NewPage->set_table(PREFIX_TABLES.'ws_pages');
				$NewPage->set_where('id="'.$id_include.'"');
				$NewPage->set_update('file',	$file);
				$NewPage->salvar();
				echo "sucesso";exit;
			};

		}elseif((!$Se_file_BD_existe || $registro_atual_vaziu) && !$Se_novo_file_existe && !$Registro_Em_Outros_Tools  &&    $info=="renomear"){
			if(file_put_contents(ROOT_WEBSITE.'/'.$file,'<?'.PHP_EOL.'# WebSheep;'.PHP_EOL.'# Novo arquivo;'.PHP_EOL.'?>')){
				$NewPage 					= new MySQL();
				$NewPage->set_table(PREFIX_TABLES.'ws_pages');
				$NewPage->set_where('id="'.$id_include.'"');
				$NewPage->set_update('file',	$file);
				$NewPage->salvar();
				echo "sucesso";exit;
			};
	}elseif($Registro_Em_Outros_Tools &&  $info=="bd"){
			echo ws::getlang('urlIncludes>modal>save>serverExists');
	}elseif(!$Registro_Em_Outros_Tools &&  $info=="bd"){
				$NewPage 					= new MySQL();
				$NewPage->set_table(PREFIX_TABLES.'ws_pages');
				$NewPage->set_where('id="'.$id_include.'"');
				$NewPage->set_update('file',	$file);
				$NewPage->salvar();
				echo "sucesso";exit;
	}elseif($Se_novo_file_existe || $Registro_Em_Outros_Tools){
			echo ws::getlang('urlIncludes>modal>save>toolExists');
	}
}

function OrdenaItem(){
	global $session;
	$array_id 		= $_REQUEST['ids'];
	$array_posicoes = $_REQUEST['posicoes'];
	$i=0;
	foreach($array_id as $id){
		if($id=="0"){
			$Salva					= new MySQL();
			$Salva->set_table(PREFIX_TABLES.'setupdata');
			$Salva->set_where('id="1"');
			$Salva->set_update('processoURL',$array_posicoes[$i]);
			$Salva->salvar();
		}else{
			$Salva					= new MySQL();
			$Salva->set_table(PREFIX_TABLES.'ws_pages');
			$Salva->set_where('id="'.$id.'"');
			$Salva->set_update('posicao',$array_posicoes[$i]);
			$Salva->salvar();
		}
		++$i;
	}
}

function addFile(){
			global $session;
			$token = _token(PREFIX_TABLES.'ws_pages','token');
			$I 					= new MySQL();
			$I->set_table(PREFIX_TABLES.'ws_pages');
			$I->set_insert('token',$token);
			$I->set_insert('type','include');
			if($I->insert()){echo "sucesso";}
}

function salvaTemplate(){
			global $session;
			global $_conectMySQLi_;
			$I 					= new MySQL();
			$I->set_table(PREFIX_TABLES.'ws_template');
			$I->set_where('id="'.$_REQUEST['id_template'].'"');
			$I->set_update('slug',$_REQUEST['slug']);
			$I->set_update('obs',$_REQUEST['obs']);
			$I->set_update('template',mysqli_real_escape_string($_conectMySQLi_,$_REQUEST['template']));
			if($I->salvar()){
				echo "sucesso";
				exit;
			}
		}

function excluiRegistro(){
			global $session;
			$I 					= new MySQL();
			$I->set_table(PREFIX_TABLES.'ws_pages');
			$I->set_where('id="'.$_REQUEST['id_include'].'"');
			if($I->exclui()){
				echo "sucesso";
				exit;
			}
		}
function excluiRegistroFile(){
	global $session;
	if($_REQUEST['registro']=='false'){
		excluiFile:
		$I 					= new MySQL();
		$I->set_table(PREFIX_TABLES.'ws_pages');
		$I->set_where('id="'.$_REQUEST['id_include'].'"');
		if($I->exclui()){
			echo "sucesso";
		};
	}else{
		$I 					= new MySQL();
		$I->set_table(PREFIX_TABLES.'ws_pages');
		$I->set_where('id="'.$_REQUEST['id_include'].'"');
		$I->select();
		$file =  $I->fetch_array[0]['file'];

		if(@unlink(ROOT_WEBSITE.'/'.$file)){
			goto excluiFile;
		}else{
			echo "Falha ao excluir o arquivo =(";
		}
	};
	exit;
}





//####################################################################################################################
//####################################################################################################################
//####################################################################################################################
//####################################################################################################################
//####################################################################################################################
//####################################################################################################################
//####################################################################################################################
//####################################################################################################################
ob_start();
include_once(__DIR__.'/../../Lib/class-ws-v1.php');
$session = new session();
ob_end_clean();
_exec($_REQUEST['function']);
?>

