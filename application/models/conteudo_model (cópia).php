<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Conteudo_Model extends CI_Model
{
	var $codigo;
	var $codigo_base;
	var $titulo;
	var $descricao;
	var $data_hora;
	var $situacao;
	var $tipo;
	var $publico;

	public function search($inicio = 0, $limite = null)
	{
		$where = array();
		if ($this->codigo)
			$where['codigo'] = $this->codigo;
		if ($this->codigo_base)
			$where['codigo_base'] = $this->codigo_base;
		if ($this->titulo)
			$where['titulo'] = $this->titulo;
		if ($this->descricao)
			$where['descricao'] = $this->descricao;
		if ($this->data_hora)
			$where['data_hora'] = $this->data_hora;
		if ($this->situacao)
			$where['situacao'] = $this->situacao;
		if ($this->tipo)
			$where['tipo'] = $this->tipo;
		if ($this->publico)
			$where['publico'] = $this->publico;

		$qry = $this->db->get_where('conteudos', $where, $limite, $inicio);
		
		$conteudo = $qry->result(); 

		return $conteudo;
	}

	public function get()
	{
		$conteudo = $this->search()[0];

		return $conteudo;
	}

	public function getAll()
	{
		$qry = $this->db->get('conteudos');
		return $qry->result();
	}

	public function join($tabela)
	{
		$conteudo = $this->get();

		if(isset($tabela['bases']))
		{
			$this->load->model("base_model", "Base");
			$this->Base->codigo = $conteudo->codigo_base;

			if (is_array($tabela['bases']))
				$base = $this->Base->join($tabela['bases']);	
			else	
				$base = $this->Base->get();			

			$conteudo->base = $base;
		}
		if (isset($tabela['arquivos']))
		{
			$this->load->model("arquivo_model", "Arquivo");
			$this->Arquivo->codigo_conteudo = $conteudo->codigo;
			
			if (is_array($tabela['arquivos']))
				$arquivo = $this->Arquivo->join($tabela['arquivos']);
			else	
				$arquivo = $this->Arquivo->get();
			$conteudo->arquivo = $arquivo;
		}
		if (isset($tabela['revisoes']))
		{
			$this->load->model("revisao_model", "Revisao");
			$this->Revisao->codigo_conteudo = $conteudo->codigo;
			if (is_array($tabela['revisoes']))
				$revisoes = $this->Revisao->join($tabela['revisoes']);
			else	
				$revisoes = $this->Revisao->search();
			$conteudo->revisoes = $revisoes;
		}
		if (isset($tabela['comentarios']))
		{
			$this->load->model("comentario_model", "Comentario");
			$this->Comentario->codigo_conteudo = $conteudo->codigo;

			if (is_array($tabela['comentarios']))
				$comentarios = $this->Comentario->join($tabela['comentarios']);
			else	
				$comentarios = $this->Comentario->search();
			
			$conteudo->comentarios = $comentarios;
		}
		if (isset($tabela['livros']))
		{
			$this->load->model("livro_model", "Livro");
			$this->Livro->codigo_conteudo = $conteudo->codigo;

			if (is_array($tabela['livros']))
				$livro = $this->Livro->join($tabela['livros']);
			else
				$livro = $this->Livro->get();

			$conteudo->livro = $livro;
		}
		if (isset($tabela['perguntas']))
		{
			$this->load->model("pergunta_model", "Pergunta");
			$this->Pergunta->codigo_conteudo = $conteudo->codigo;

			if (is_array($tabela['perguntas']))
				$pergunta = $this->Pergunta->join($tabela['perguntas']);
			else
				$pergunta = $this->Pergunta->get();

			$conteudo->pergunta = $pergunta;
		}
		if (isset($tabela['links']))
		{
			$this->load->model("link_model", "Link");
			$this->Link->codigo_conteudo = $conteudo->codigo;

			if (is_array($tabela['links']))
				$link = $this->Link->join($tabela['artigos']);
			else
				$link = $this->Link->get();

			$conteudo->link = $link;
		}
		if (isset($tabela['artigos']))
		{
			$this->load->model("artigo_model", "Artigo");
			$this->Artigo->codigo_conteudo = $conteudo->codigo;
			
			if (is_array($tabela['artigos']))
				$artigo = $this->Artigo->join($tabela['artigos']);
			else
				$artigo = $this->Artigo->get();
			
			$conteudo->artigo = $artigo;
		}

		return $conteudo;
	}

	public function insert()
	{
		$data = array(
			'codigo' => $this->codigo,
			'codigo_base' => $this->codigo_base,
			'titulo' => $this->titulo,
			'descricao' => $this->descricao,
			'data_hora' => $this->data_hora,
			'situação' => $this->situação,
			'tipo' => $this->tipo,
			'publico' => $this->publico
		);
		$this->db->insert('conteudos', $data);
	}

	public function update()
	{
		$data = array();
		if ($this->codigo_base)
			$data['codigo_base'] = $this->codigo_base;
		if ($this->titulo)
			$data['titulo'] = $this->titulo;
		if ($this->descricao)
			$data['descricao'] = $this->descricao;
		if ($this->data_hora)
			$data['data_hora'] = $this->data_hora;
		if ($this->situacao)
			$data['situacao'] = $this->situacao;
		if ($this->tipo)
			$data['tipo'] = $this->tipo;
		if ($this->publico)
			$data['publico'] = $this->publico;

		$this->db->where('codigo', $this->codigo);
		$this->db->update('conteudos', $data);
	}

	public function delete()
	{
		$this->db->delete('conteudos', $this->conteudo);

		$this->load->model('base_model', 'Base');
		$this->Base->codigo = $this->codigo_base;
		$this->Base->delete();
	}

	public function like($parametro_especifico)
	{
		$parametro = $parametro_especifico;
		$this->db->like('titulo', $parametro);
		$this->db->or_like('descricao', $parametro);
		$resultado = $this->db->get('conteudos')->result();
		
		$artigos = array(); $livros = array();
		$videos = array(); $perguntas = array();
		$imagens = array(); $links = array();
		$audios = array(); $outros = array();

		for ($i=0; $i < count($resultado); $i++) 
		{ 
			if($resultado[$i]->codigo_base == $this->codigo_base)
			{
				switch ($resultado[$i]->tipo) 
				{
					case 1: array_push($artigos, $resultado[$i]); break;
					case 2: array_push($videos, $resultado[$i]); break;
					case 3: array_push($imagens, $resultado[$i]); break;
					case 4: array_push($audios, $resultado[$i]); break;
					case 5: array_push($livros, $resultado[$i]); break;
					case 6: array_push($perguntas, $resultado[$i]); break;
					case 7: array_push($links, $resultado[$i]); break;
					case 8: array_push($outros, $resultado[$i]); break;
				}
			}
		}

		
		$conteudos = array();

		if(count($artigos) > 0)
			$conteudos["artigos"] = $artigos;
		if( count($videos) > 0)
			$conteudos["videos"] = $videos;
		if(count($imagens) > 0)
			$conteudos["imagens"] = $imagens;
		if( count($audios) > 0)
			$conteudos["audios"] = $audios;
		if( count($livros) > 0)
			$conteudos["livros"] = $livros;
		if(count($perguntas) > 0)
			$conteudos["perguntas"] = $perguntas;
		if( count($links) > 0)
			$conteudos["links"] = $links;
		if(count($outros) > 0)
			$conteudos["outros"] = $outros;

		return $conteudos;
	}
	
	public function mais_acessados()
	{
		$this->db->select('codigo_conteudo, count(*) as "total"');
		$this->db->group_by("codigo_conteudo"); 
		$this->db->order_by("total", "desc"); 
		$visitas = $this->db->get_where('visitas', array('codigo_base' => $this->codigo_base));
		$visitas = $visitas->result();
		$mais_acessados = array();

		for ($i=0; $i < count($visitas); $i++) 
		{ 
			$this->codigo = $visitas[$i]->codigo_conteudo;
			$conteudo = $this->get();
			$visitas[$i]->titulo = $conteudo->titulo;
			$visitas[$i]->tipo = $conteudo->tipo;
		}

		return $visitas;
	}	
}
?>