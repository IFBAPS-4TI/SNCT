<?php


class DatabaseHandler
{
    protected $config;
    private $pdo;
    private $tables;

    /**
     * DatabaseHandler constructor.
     */
    public function __construct()
    {
        $this->config = new DatabaseConfig();
        $this->pdo = $this->config->getPdo();
        $this->tables = new DatabaseTables();
    }

    public function alterarSenha(\Models\Usuario $usuario, $novaSenha)
    {
        $data = $this->getDataByEmail($usuario->getEmail());
        if (count($data) > 1 && $data['cpf'] == $usuario->getCpf()) {
            // Usuário encontrado
            $update = $this->pdo->update(array('senha' => $usuario->getSenha()))
                ->table($this->tables->getUsuarios())
                ->whereMany(array('email' => $usuario->getEmail(), 'cpf' => $usuario->getCpf()), '=');
            $afetadas = $update->execute();
            if (count($afetadas) > 0) {
                $email = new \SendGrid\Mail\Mail();
                $email->setFrom("noreply@example.org", "IFBA - Porto Seguro");
                $email->setSubject("SNCT - Nova senha");
                $email->addTo($usuario->getEmail(), "Usuário");
                $email->addContent(
                    "text/html", "Sua nova senha é <strong>{$novaSenha}</strong>. Lembre-se que você pode altera-la a qualquer momento no seu painel."
                );
                $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
                try {
                    $sendgrid->send($email);
                } catch (Exception $e) {
                    return false;
                }
            } else {
                return false;
            }

        } else {
            throw new Exception("Usuário não encontrado.");
        }
        return true;
    }

    public function getCertificadosByUsuario($id_usuario){
        $inscricoes = $this->getInscricoesByIdUsuario($id_usuario, $ignoreAntigas = false);
        $certificados = array();
        // Primeiro as inscrições
        foreach($inscricoes as $inscricao){
            if($inscricao['compareceu'] == 1 && $inscricao['certificado'] == 1 && $this->ativAcabou($inscricao['id_atividade'])){
                $certificado = array();
                $certificado['id_atividade'] = $inscricao['id_atividade'];
                $certificado['id_sessao'] = $inscricao['id_sessao'];
                $certificado['nome'] = $inscricao['nome'];

                $certificado['timestamp_ativ'] = $inscricao['timestamp_ativ'];
                $certificado['duracao'] = $inscricao['duracao'];
                $certificado['tipo_org'] = 1; // Visitante = 1, Organizador = 2, Monitor = 3;
                $token = array(
                    "id_usuario" => $id_usuario,
                    "id_atividade" => $inscricao['id_atividade'],
                    "id_sessao" => $inscricao['id_sessao'],
                    "tipo" => 1,
                    "iss" => $_SERVER['SERVER_NAME']
                );
                $certificado['hash'] = base64_encode(Util::encodeToken($token)); // Assinando os certificados
                $certificados[] = $certificado;
            }
        }
        $session = new \SlimSession\Helper;
        $token = (array) Util::decodeToken($session->get('jwt_token'));
        $usuario = $this->TokenTranslation($token);
        // Monitorias e Organizadores
        foreach($usuario->getMonitorias() as $monitoria){
            if($this->ativAcabou($monitoria)){
                $sessoes = $this->getSessaoDataById($monitoria);
                $dados = $this->getAtivDataById($monitoria);
                foreach($sessoes as $sessao){
                    $certificado = array();
                    $certificado['id_atividade'] = $dados['id_atividade'];
                    $certificado['id_sessao'] = $sessao['id_sessao'];
                    $certificado['nome'] = $dados['nome'];
                    $certificado['timestamp_ativ'] = $inscricao['timestamp_ativ'];
                    $certificado['duracao'] = $inscricao['duracao'];
                    $dadosMonitores = $this->getMonitorDataByAtividade('id_atividade');
                    $tipo = 3;
                    foreach($dadosMonitores as $monitor){
                        if($monitor['id_usuario'] == $usuario->getId() && $monitor['organizador'] == 1){
                            $tipo = 2;
                        }
                    }
                    $certificado['tipo_org'] = $tipo; // Visitante = 1, Organizador = 2, Monitor = 3;
                    $token = array(
                        "id_usuario" => $id_usuario,
                        "id_atividade" => $inscricao['id_atividade'],
                        "id_sessao" => $inscricao['id_sessao'],
                        "tipo" => $tipo,
                        "iss" => $_SERVER['SERVER_NAME']
                    );
                    $certificado['hash'] = base64_encode(Util::encodeToken($token)); // Assinando os certificados
                    $certificados[] = $certificado;
                }
            }
        }
        return $certificados;
    }

    public function criarUsuario(\Models\Usuario $usuario)
    {
        // Verifica se email ou cpf do usuário já existe
        $select = $this->pdo->select()
            ->from($this->tables->getUsuarios())
            ->where('email', '=', $usuario->getEmail())->orWhere('cpf', "=", $usuario->getCpf());
        $stmt = $select->execute();
        $data = $stmt->fetch();
        if (count($data) > 1) { # Ele tem uma row vazia
            throw new Exception("Email ou CPF do usuário já existe.");
        }

        // Tudo certo, inserir usuário
        $insert = $this->pdo->insert(array('nome', 'email', 'nascimento', 'cpf', 'senha'))
            ->into($this->tables->getUsuarios())
            ->values(array($usuario->getNome(), $usuario->getEmail(), $usuario->getNascimento(), $usuario->getCpf(), $usuario->getSenha()));
        return $insert->execute(false);
    }

    public function getDataByEmail($email)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getUsuarios())
            ->where('email', '=', $email);
        $stmt = $select->execute();
        return $stmt->fetch();
    }

    public function getDataById($id_usuario)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getUsuarios())
            ->where('id_usuario', '=', $id_usuario);
        $stmt = $select->execute();
        return $stmt->fetch();
    }

    public function getAtivDataById($id_atividade)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getAtividades())
            ->where('id_atividade', '=', $id_atividade);
        $stmt = $select->execute();
        return $stmt->fetch();
    }

    public function getSessaoDataById($id_atividade)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getSessoes())
            ->where('id_atividade', '=', $id_atividade);
        $stmt = $select->execute();
        return $stmt->fetchAll();
    }

    public function getSessaoDataByIdSessao($id_sessao)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getSessoes())
            ->where('id_sessao', '=', $id_sessao);
        $stmt = $select->execute();
        return $stmt->fetch();
    }

    public function getMonitorDataByAtividade($id_atividade)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getMonitores())
            ->where('id_atividade', '=', $id_atividade);
        $stmt = $select->execute();
        return $stmt->fetchAll();
    }
    public function getMonitorDataByIdUsuario($id_usuario)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getMonitores())
            ->where('id_usuario', '=', $id_usuario);
        $stmt = $select->execute();
        return $stmt->fetchAll();
    }
    public function checkAdmin($id_usuario)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getAdministradores())
            ->where('id_usuario', '=', $id_usuario);
        $stmt = $select->execute();
        return $stmt->fetch();
    }

    public function authUsuario(\Models\Usuario $usuario)
    {
        $data = $this->getDataByEmail($usuario->getEmail());
        if (count($data) > 1 && crypt($usuario->getSenha(), $data['senha']) == $data['senha']) {
            $usuario->setNascimento($data['nascimento']);
            $usuario->setCpf($data['cpf']);
            $usuario->setId($data['id_usuario']);
            if (count($this->checkAdmin($data['id_usuario'])) > 1) {
                $usuario->setIsAdministrador(true);
            }
            $usuario->setNome($data['nome']);
            $usuario->erasePass(); # Apagar senha, pq não precisamos mais dela.
        } else {
            throw new Exception("Usuário ou senha incorretos.");
        }
        return $usuario;
    }

    public function TokenTranslation($token)
    {
        $usuario = new \Models\Usuario();
        $data = $this->getDataByEmail($token['email']);
        if (count($data) > 1) {
            $usuario->setNascimento($data['nascimento']);
            $usuario->setCpf($data['cpf']);
            $usuario->setId($data['id_usuario']);
            $usuario->setEmail($token['email']);
            if (count($this->checkAdmin($data['id_usuario'])) > 1) {
                $usuario->setIsAdministrador(true);
            } else {
                $usuario->setIsAdministrador(false);
            }
            $monitorias = array();
            $monitoriadata = $this->getMonitorDataByIdUsuario($data['id_usuario']);
            foreach($monitoriadata as $monitoria){
                $monitorias[] = $monitoria['id_atividade'];
            }
            $usuario->setMonitorias($monitorias);
            $usuario->setNome($data['nome']);
            return $usuario;
        } else {
            session_destroy();
            header("Location:", "/painel");
            return null;
        }
    }

    public function addAdmin($id_usuario)
    {
        $insert = $this->pdo->insert(array('id_usuario'))
            ->into($this->tables->getAdministradores())
            ->values(array($id_usuario));
        return $insert->execute(false);
    }

    public function listAdmin()
    {
        $select = $this->pdo->select()
            ->from($this->tables->getAdministradores());
        $stmt = $select->execute();
        $data_admin = $stmt->fetchAll();
        $data = array();
        foreach ($data_admin as $admin) {

            $data[] = $this->getDataById($admin['id_usuario']);
        }
        return $data;
    }

    public function listUsers()
    {
        $select = $this->pdo->select()
            ->from($this->tables->getUsuarios());
        $stmt = $select->execute();
        return $stmt->fetchAll();;
    }

    public function listAtividades($ignorarAntigas = true)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getAtividades());
        $stmt = $select->execute();
        $ativs = $stmt->fetchAll();
        $resultados = array();
        if($ignorarAntigas){
            foreach($ativs as $ativ){
                if(!$this->ativAcabou($ativ['id_atividade'])){
                    $resultados[] = $ativ;
                }
            }
        }

        return $resultados;
    }
    public function ativAcabou($ativ_id){
        $select = $this->pdo->select()
            ->from($this->tables->getSessoes())
            ->where('id_atividade', '=', $ativ_id);
        $stmt = $select->execute();
        $sessoes = $stmt->fetchAll();
        $acabou = true;
        foreach($sessoes as $sessao){
            $timestamp_o = $sessao['timestamp_ativ'];
            $data = str_replace('/', '-', $timestamp_o);
            $data = strtotime(date('d-m-Y', strtotime($data)));
            $min = strtotime('+14 days', $data);
            if (time() < $min) {
                $acabou = false;
            }
        }
        return $acabou;
    }
    public function listSessoesPorId($id)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getSessoes())
            ->where('id_atividade', '=', $id);
        $stmt = $select->execute();
        return $stmt->fetchAll();;
    }

    public function removeAdmin($id_usuario)
    {
        $deleteStatement = $this->pdo->delete()
            ->from($this->tables->getAdministradores())
            ->where('id_usuario', '=', $id_usuario);

        return $deleteStatement->execute();
    }

    public function removeUser($id_usuario)
    {
        $deleteStatement = $this->pdo->delete()
            ->from($this->tables->getUsuarios())
            ->where('id_usuario', '=', $id_usuario);

        return $deleteStatement->execute();
    }
    public function removeInscricao($id_inscricao)
    {
        $deleteStatement = $this->pdo->delete()
            ->from($this->tables->getInscricoes())
            ->where('id_inscricao', '=', $id_inscricao);

        return $deleteStatement->execute();
    }
    public function removeInscricaoTrava($id_inscricao, $id_usuario)
    {
        $deleteStatement = $this->pdo->delete()
            ->from($this->tables->getInscricoes())
            ->whereMany(array('id_inscricao' => $id_inscricao, 'id_usuario' => $id_usuario), "=");

        return $deleteStatement->execute();
    }
    public function removeMonitorFromAtiv($id_usuario, $id_ativ)
    {
        $deleteStatement = $this->pdo->delete()
            ->from($this->tables->getMonitores())
            ->whereMany(array('id_usuario' => $id_usuario, 'id_atividade' => $id_ativ), '=');

        return $deleteStatement->execute();
    }
    public function removeAtiv($id)
    {
        $deleteStatement = $this->pdo->delete()
            ->from($this->tables->getAtividades())
            ->where('id_atividade', '=', $id);

        return $deleteStatement->execute();
    }

    public function removeSession($id)
    {
        $deleteStatement = $this->pdo->delete()
            ->from($this->tables->getSessoes())
            ->where('id_sessao', '=', $id);

        return $deleteStatement->execute();
    }

    public function addAtividade(\Models\Atividade $atividade)
    {

        $insert = $this->pdo->insert(array('nome', 'descricao', 'certificado', 'tipo', 'capacidade', 'duracao'))
            ->into($this->tables->getAtividades())
            ->values(array($atividade->getNome(), $atividade->getDescricao(), (int)$atividade->isCertificado(),
                $atividade->getTipo(), $atividade->getCapacidade(), $atividade->getDuracao()));
        $insert_id = $insert->execute(true);

        if ($atividade->getOrganizador() === 0 || $atividade->getOrganizador() == null || $atividade->getOrganizador() == "") {
            $session = new \SlimSession\Helper;
            $token = (array)Util::decodeToken($session->get('jwt_token'));
            $usuario = $this->TokenTranslation($token);
            $insert = $this->pdo->insert(array('id_usuario', 'id_atividade', 'organizador'))
                ->into($this->tables->getMonitores())
                ->values(array($usuario->getId(), $insert_id, 1));
        } else {
            $organizador = $this->getDataByEmail($atividade->getOrganizador());
            $insert = $this->pdo->insert(array('id_usuario', 'id_atividade', 'organizador'))
                ->into($this->tables->getMonitores())
                ->values(array($organizador['id_usuario'], $insert_id, 1));

        }
        $insert->execute(false);
        if ($insert_id) {
            $sessoes = $atividade->getSessoes();
            foreach ($sessoes as $sessao) {
                $insert = $this->pdo->insert(array('id_atividade', 'local_ativ', 'timestamp_ativ'))
                    ->into($this->tables->getSessoes())
                    ->values(array($insert_id, $sessao->getLocal(), $sessao->buildTimestamp()));
                if (!$insert->execute(false)) {
                    throw new Exception("Não foi possível adicionar uma das sessões. Ela já existe ou é inválida.");
                }

            }

        } else {
            throw new Exception("Não foi possível adicionar a atividade. Ela já existe ou é inválida.");
        }
        return true;
    }

    /**
     * @param \Models\Atividade $atividade
     * @throws Exception
     */
    public function editAtiv(\Models\Atividade $atividade)
    {
        if (count($this->getAtivDataById($atividade->getId())) > 1) {
            $dados = array('nome' => $atividade->getNome(), 'descricao' => $atividade->getDescricao(), 'certificado' => (int)$atividade->isCertificado(),
                'capacidade' => $atividade->getCapacidade(), 'duracao' => $atividade->getDuracao());
            $update = $this->pdo->update($dados)
                ->table($this->tables->getAtividades())
                ->where('id_atividade', '=', $atividade->getId());
            if ($update->execute() < 1) {
                throw new Exception("Algo deu errado ao atualizar a atividade");
            }
        } else {
            throw new Exception("Atividade não foi encontrada");
        }
    }
    public function editAtivMonitor(\Models\Atividade $atividade)
    {
        if (count($this->getAtivDataById($atividade->getId())) > 1) {
            $dados = array('nome' => $atividade->getNome(), 'descricao' => $atividade->getDescricao());
            $update = $this->pdo->update()
                ->set($dados)
                ->table($this->tables->getAtividades())
                ->where('id_atividade', '=', (int)$atividade->getId());
            if ($update->execute() < 1) {
                throw new Exception("Algo deu errado ao atualizar a atividade");
            }
        } else {
            throw new Exception("Atividade não foi encontrada");
        }
    }

    public function editOrCreateSession(\Models\Sessao $sessao, $insertId = 0)
    {
        if ($sessao->getId() != 0) {
            if (count($this->getSessaoDataByIdSessao($sessao->getId())) > 1) {
                $update = $this->pdo->update(array('local_ativ' => $sessao->getLocal(), 'timestamp_ativ' => $sessao->buildTimestamp()))
                    ->table($this->tables->getSessoes())
                    ->where('id_sessao', '=', $sessao->getId());
                if ($update->execute() < 1) {
                    throw new Exception("Algo deu errado ao atualizar a sessão");
                }
            } else {
                throw new Exception("Atividade não foi encontrada");
            }
        } else {
            if ($insertId != 0) {
                $insert = $this->pdo->insert(array('id_atividade', 'local_ativ', 'timestamp_ativ'))
                    ->into($this->tables->getSessoes())
                    ->values(array($insertId, $sessao->getLocal(), $sessao->buildTimestamp()));
                if (!$insert->execute(false)) {
                    throw new Exception("Não foi possível adicionar uma das sessões. Ela já existe ou é inválida.");
                }
            } else {
                throw new Exception("Você deve fornecer o id da atividade para criar uma sessão");
            }
        }
    }

    public function getInscritosBySessionId($id_sessao)
    {
        $select = $this->pdo->select()
            ->from($this->tables->getInscricoes())
            ->where('id_sessao', '=', $id_sessao);
        $stmt = $select->execute();
        return $stmt->fetchAll();
    }
    public function addMonitor($id_usuario, $id_ativ, $organizador = false){
        if($organizador){
            $organizador = 1;
        }else{
            $organizador = 0;
        }
        $insert = $this->pdo->insert(array('id_atividade', 'id_usuario', 'organizador'))
            ->into($this->tables->getMonitores())
            ->values(array($id_ativ, $id_usuario, $organizador));
        if (!$insert->execute(false)) {
            throw new Exception("Não foi possível adicionar uma das sessões. Ela já existe ou é inválida.");
        }
    }
    public function atualizarFrequencia($id_inscricao, $valor){
            if($valor != 1 && $valor != 0){
                throw new Exception("Impossível mudar estado da inscrição.");
            }
            $dados = array('compareceu' => (int)$valor);
            $update = $this->pdo->update($dados)
                ->table($this->tables->getInscricoes())
                ->where('id_inscricao', '=', $id_inscricao);
            if ($update->execute() < 1) {
                throw new Exception("Algo deu errado ao atualizar a inscrição.");
            }

    }
    public function getInscricoesByIdUsuario($id_usuario, $ignoreAntigas = true){
        $select = $this->pdo->select()
            ->from($this->tables->getInscricoes())
            ->join($this->tables->getSessoes(), "{$this->tables->getInscricoes()}.id_sessao", '=', "{$this->tables->getSessoes()}.id_sessao")
            ->join($this->tables->getAtividades(), "{$this->tables->getAtividades()}.id_atividade", '=', "{$this->tables->getSessoes()}.id_atividade")
            ->where("{$this->tables->getInscricoes()}.id_usuario", '=', $id_usuario);
        $stmt = $select->execute();
        $dados = $stmt->fetchAll();
        $resultados = array();
        if($ignoreAntigas){
            foreach($dados as $dado){
                if(!$this->ativAcabou($dado['id_atividade'])){
                    $resultados[] = $dado;
                }
            }
        }else{
            $resultados = $dados;
        }
        return $resultados;
    }
    public function addInscricao($id_usuario, $id_sessao){
        $insert = $this->pdo->insert(array('id_usuario', 'id_sessao', 'compareceu'))
            ->into($this->tables->getInscricoes())
            ->values(array($id_usuario, $id_sessao, 0));
        return $insert->execute(false);
    }
    public function atualizarPerfil(\Models\Usuario $usuario){
        $dados = array();
        $dados['email'] = $usuario->getEmail();
        if($usuario->getSenha() != null || $usuario->getSenha() != ""){
            $dados['senha'] = $usuario->getSenha();
        }
        $update = $this->pdo->update($dados)
            ->table($this->tables->getUsuarios())
            ->where('id_usuario', '=', $usuario->getId());
        if ($update->execute() < 1) {
            throw new Exception("Algo deu errado ao atualizar o perfil");
        }
    }
}