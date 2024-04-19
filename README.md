Criando o Order Service

Para rodar o projeto deve ter isntalado o php 8.2

composer create-project laravel/laravel api-service

Criando o Location Service
Criamos então mais um serviço chamado Location-service para que consigamos mais a frente fazer a comunicação de ambos usando mensageria.

composer create-project laravel/laravel api-service
Testando os projetos Laravel localmente
Quando o composer terminar de instalar cada projeto, você navega via cmd até a pasta raiz de ambos e utiliza o comando abaixo. Com isso você conseguirá acessar a linha de comando do Laravel que será importante para nossos testes.

php artisan tinker
Subindo o RabbitMQ 

Ao rodar, será baixada a versão da imagem do RabbitMQ passada.
No meu caso ao acessar localhost:15672 e passar usuário: guest e senha: guest, consegui entrar no manager do RabbitMQ.

Tela inicial do RabbitMQ.
Agora que criamos os serviços e subimos o broker vamos avançar mais um pouco. Inicialmente iremos instalar a biblioteca mirabel-rabbitmq que é baseada na excepcional biblioteca php-amqplib. Através dela vamos precisar de pouquíssima configuração e depois precisaremos simplesmente criar as camadas de consumidores e/ou produtores. Para a instalação, rode o comando abaixo na pasta raiz de seus serviços.

Criando os produtores e consumidores
Produtor do Serviço de Pedidos
A primeira coisa a se fazer é definir uma camada para os produtores. Você tem a liberdade de chamar como for, porém neste exemplo adicionamos uma pasta “Events” e nela começou a criar os produtores que necessariamente tem que compor a classe RabbitMQEventsConnection da bilioteca mirabel-rabbitmq, bastando fazer um “use” dela como no exemplo. Não esqueça de definir o namespace , no nosso exemplo é “App\Events”.

Ainda sobre esse exemplo, nossos produtores precisam de uma chave de roteamento , então definimos com uma constante const ROUTING_KEY = 'order-services.order.received' e no construtor de nossa classe passamos o payload que pode ser um json , string , objeto, etc.

<?php 

namespace App\Events; 

use Pablicio\MirabelRabbitmq\RabbitMQEventsConnection; 

class OrderReceivedEvent
{ 
  use RabbitMQEventsConnection; 

  const ROUTING_KEY = 'order-services.order.received'; 

  function __construct($payload)
   { 
    $this->routingKey = self::ROUTING_KEY; 
    $this->payload = $payload; 
  } 
}
Consumer do Order Service para a routing key: ‘test-services.order.done’
<?php

namespace App\Workers;

use Pablicio\MirabelRabbitmq\RabbitMQWorkersConnection;

class TestOrderDoneWorker
{
  use RabbitMQWorkersConnection;

  const QUEUE = 'order-services.order-test.done',
    routing_keys = [
      'test-service.order.done'
    ],
    options = [
      'exchange_type' => 'topic'
    ],
    retry_options = [
      'x-message-ttl' => 1000,
      'max-attempts' => 8
    ];

  public function work($msg)
  {
    try {
      print_r($msg->body);

      return $this->ack($msg);
    } catch (\Exception $e) {

      return $this->nack($msg);
    }
  }
}
Os workers tem a estrutura definida como no exemplo abaixo, onde criamos eles na pasta Workers, damos um nome qualquer para as filas (QUEUE) e definimos quais os eventos que eles escutam no array de “routing_keys”, podendo ser uma ou várias. Ah! Também compomos a classe use RabbitMQWorkersConnection como fizemos com a dos eventos. Outro atributo importante é o de “options” onde passamos as configurações de nossa fila e do “retry_options” onde definimos o número de retentativas e qual o tempo para cada “retentativa” e as demais configurações da nossa fila de retry. Se esgotar a quantidade de tentativas e a mensagem não for processada ela vai definitivamente para a fila dos erros. Falando nisso, quando criamos a fila “order-services.order-test.done”, a propria lib cria uma exchange com o mesmo nome da fila e cria outras duas filas com suas respectivas exchanges de mesmo nome para as “Retentativas” e “Erro”. Exchanges são responsáveis por enviar as mensagens para as filas, através das routing keys.

A capacidade de reprocessamento de mensagens é um dos principais benefícios da arquitetura orientada a eventos, pois permite que o sistema se torne confiável, tolerante a falhas e também se torne escalável uma vez que vc pode definir “n” workers para processar as mensagens enfileiradas. Se depois de todas as retentativas não for possível processar a mensagem, ela ainda estará na fila de erros para quando o serviço for corrigido e voltar a funcionar possamos tentar novamente. É impensável para grandes empresas hoje em dia não ter esse poder.

order-services.order-test.done
order-services.order-test.done.retry
order-services.order-test.done.error
Sem se aprofundar muito, vemos as filas criadas e já com as configurações necessárias para os três casos possíveis nas nossas mensagens. “tentar”, “tentar novamente quantas vezes for preciso”, “aceitar que deu ruim”.

