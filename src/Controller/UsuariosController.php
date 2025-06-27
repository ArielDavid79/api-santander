<?php

namespace App\Controller;

use App\Dto\UsuaarioContaDto;
use App\Dto\UsuarioContaDto;
use App\Dto\UsuarioDto;
use App\Entity\Conta;
use App\Entity\Usuario;
use App\Repository\ContaRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class UsuariosController extends AbstractController
{
    #[Route('/usuarios', name: 'usuarios_criar',methods:['POST'])]
    public function criar(
        #[MapRequestPayload(acceptFormat: 'json')]
        UsuarioDto $usuarioDto,
        UsuarioRepository $usuario_repository,

        EntityManagerInterface $entitymanager
    ): JsonResponse
    {

        $erros = [];

        
        //validar os dados do DTO
       

        if(!($usuarioDto->getCpf())){
            array_push($erros, [
                'message' =>'cpf é obrigatorio'
            ]);
        }
        if(!($usuarioDto->getNome())){
            array_push($erros, [
                'message' =>'Nome é obrigatorio'
            ]);
        }

         if(!($usuarioDto->getEmail())){
            array_push($erros, [
                'message' =>'E-mail é obrigatorio'
            ]);
        }
         if(!($usuarioDto->getTelefone())){
            array_push($erros, [
                'message' =>'Telefone é obrigatorio'
            ]);
        }
         if (count($erros) > 0) {
            return $this-> json($erros, 422);
        }

        //valida se o suario ja esta cadastrado

        $usuarioExistente = $usuario_repository->findByCpf($usuarioDto ->getCpf());
            if($usuarioExistente){ 
                return $this -> json(["msagem" => "cpf ja cadastrado"],409);
            }
         
         // convert o DTO em entidade usuario

         $usuario = new Usuario();
         $usuario->setCpf($usuarioDto->getCpf());
         $usuario->setNome($usuarioDto->getNome());
         $usuario->setEmail($usuarioDto->getEmail());
         $usuario->setTelefone($usuarioDto->getTelefone());
         $usuario->setSenha($usuarioDto->getSenha());


         //criar registro na tb usuario
          $entitymanager->persist($usuario);
          $entitymanager->flush();
        
        //instanciar  o objeto conta
        $conta = new Conta();
        $numeroConta = preg_replace('/\d/','',uniqid());
        $numeroConta = rand(1,99999);
        $conta ->setNumero($numeroConta);
        $conta ->setSaldo('0');
        $conta ->setUsuario($usuario);


        //criar registro na tb conta
        $entitymanager->persist($conta);
        $entitymanager->flush();


        //retornar os dados de usuarios 

        $usuarioContaDto = new UsuarioContaDto();
        $usuarioContaDto-> setId($usuario->getId());
        $usuarioContaDto-> setNome($usuario->getNome());
        $usuarioContaDto-> setCpf($usuario->getCpf());
        $usuarioContaDto-> setEmail($usuario->getEmail());
        $usuarioContaDto-> setTelefone($usuario->getTelefone());
        $usuarioContaDto-> setNumeroConta($conta->getNumero());
        $usuarioContaDto-> setSaldo($conta->getSaldo());

                
        return $this->json($usuario);

    }
    
    #[Route("/usuarios/{id}",name:"usuario_buscar", methods: ['GET'])]

    public function buscarPorId(
        int $id,
        ContaRepository $contaRepository
    ){
        $conta =$contaRepository->findByUsuarioId($id);

        if(!$conta){
            return $this->json([
                    'message'=>'usuario não encontrado'
                ],status:404);
        }

        $usuarioContaDto = new UsuarioContaDto();
        $usuarioContaDto-> setId($conta->getUsuario()->getId());
        $usuarioContaDto-> setNome($conta->getUsuario()->getnome());
        $usuarioContaDto-> setEmail($conta->getUsuario()->getEmail());
        $usuarioContaDto-> setTelefone($conta->getUsuario()->getTelefone());
        $usuarioContaDto-> setNumeroConta($conta->getNumero());
        $usuarioContaDto-> setSaldo($conta->getSaldo());

        return $this->json($usuarioContaDto);
    } 

       

}
