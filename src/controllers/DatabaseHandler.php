<?php


class DatabaseHandler
{
    protected $config;
    private $pdo;

    /**
     * DatabaseHandler constructor.
     */
    public function __construct()
    {
        $this->config = new DatabaseConfig();
        $this->pdo = $this->config->getPdo();
    }

    public function alterarSenha(\Models\Usuario $usuario, $novaSenha)
    {
        $data = $this->getDataByEmail($usuario->getEmail());
        if (count($data) > 1 && $data['cpf'] == $usuario->getCpf()) {
            // Usuário encontrado
            $update = $this->pdo->update(array('senha' => $usuario->getSenha()))
                ->table('Usuario')
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
                $sendgrid = new \SendGrid($_SERVER['SENDGRID_API_KEY']);
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

    public function criarUsuario(\Models\Usuario $usuario)
    {
        // Verifica se email ou cpf do usuário já existe
        $select = $this->pdo->select()
            ->from('Usuario')
            ->where('email', '=', $usuario->getEmail())->orWhere('cpf', "=", $usuario->getCpf());
        $stmt = $select->execute();
        $data = $stmt->fetch();
        if (count($data) > 1) { # Ele tem uma row vazia
            throw new Exception("Email ou CPF do usuário já existe.");
        }

        // Tudo certo, inserir usuário
        $insert = $this->pdo->insert(array('nome', 'email', 'nascimento', 'cpf', 'senha'))
            ->into('Usuario')
            ->values(array($usuario->getNome(), $usuario->getEmail(), $usuario->getNascimento(), $usuario->getCpf(), $usuario->getSenha()));
        return $insert->execute(false);
    }

    public function getDataByEmail($email)
    {
        $select = $this->pdo->select()
            ->from('Usuario')
            ->where('email', '=', $email);
        $stmt = $select->execute();
        return $stmt->fetch();
    }

    public function getDataById($id_usuario)
    {
        $select = $this->pdo->select()
            ->from('Usuario')
            ->where('id_usuario', '=', $id_usuario);
        $stmt = $select->execute();
        return $stmt->fetch();
    }


    public function checkAdmin($id_usuario)
    {
        $select = $this->pdo->select()
            ->from('Administradores')
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
            if (count($this->checkAdmin($data['id_usuario'])) > 1) {
                $usuario->setIsAdministrador(true);
            } else {
                $usuario->setIsAdministrador(false);
            }
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
            ->into('Administradores')
            ->values(array($id_usuario));
        return $insert->execute(false);
    }

    public function listAdmin()
    {
        $select = $this->pdo->select()
            ->from('Administradores');
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
            ->from('Usuario');
        $stmt = $select->execute();
        return $stmt->fetchAll();;
    }
    public function listAtividades()
    {
        $select = $this->pdo->select()
            ->from('Atividade');
        $stmt = $select->execute();
        return $stmt->fetchAll();;
    }
    public function listSessoesPorId($id)
    {
        $select = $this->pdo->select()
            ->from('Sessoes')
            ->where('id_atividade', '=', $id);
        $stmt = $select->execute();
        return $stmt->fetchAll();;
    }
    public function removeAdmin($id_usuario)
    {
        $deleteStatement = $this->pdo->delete()
            ->from('Administradores')
            ->where('id_usuario', '=', $id_usuario);

        return $deleteStatement->execute();
    }

    public function removeUser($id_usuario)
    {
        $deleteStatement = $this->pdo->delete()
            ->from('Usuario')
            ->where('id_usuario', '=', $id_usuario);

        return $deleteStatement->execute();
    }

    public function removeAtiv($id)
    {
        $deleteStatement = $this->pdo->delete()
            ->from('Atividade')
            ->where('id_atividade', '=', $id);

        return $deleteStatement->execute();
    }

    public function addAtividade(\Models\Atividade $atividade)
    {

        $insert = $this->pdo->insert(array('nome', 'descricao', 'certificado', 'tipo', 'capacidade', 'duracao'))
            ->into('Atividade')
            ->values(array($atividade->getNome(), $atividade->getDescricao(), $atividade->isCertificado(),
                $atividade->getTipo(), $atividade->getCapacidade(), $atividade->getDuracao()));
        $insert_id = $insert->execute(true);
        if ($atividade->getOrganizador() != 0 && (count($this->getDataByEmail($atividade->getOrganizador())) > 1)) {
            $insert = $this->pdo->insert(array('id_usuario', 'id_atividade'))
                ->into('Monitor')
                ->values(array($insert_id, $this->getDataByEmail($atividade->getOrganizador())['email']));
        } else {
            $admins = $this->listAdmin();
            $insert = $this->pdo->insert(array('id_usuario', 'id_atividade'))
                ->into('Monitor')
                ->values(array($insert_id, $admins[0]['id_usuario']));
        }
        $insert->execute(false);
        if ($insert_id) {
            $sessoes = $atividade->getSessoes();
            foreach ($sessoes as $sessao) {
                $insert = $this->pdo->insert(array('id_atividade', 'local_ativ', 'timestamp_ativ'))
                    ->into('Sessoes')
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
}