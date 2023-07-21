Read:

UI (peut appeler n'importe quelle couche):
- controller (API REST ou page web ou cli) par exemple. Il appelle la couche Application avec un DTO (par exemple FindByEmailQuery) qui est hydraté avec la requête GET
- Puis fait un ask de FindByEmailQuery. Cet appel à ask va appeler $this->queryBus->ask()
- Cet appel à ask va faire appel à MessengerQueryBus qui fait un $this->messageBus->dispatch($query) qui appelera la classe FindByEmailHandler de la couche Application
- Cette couche appelle le template si nécessaire


Application:
Répertoire Query dans lequel se trouve le répertoire du use case (par exemple FindByEmail) et, dans ce répertoire:
- FindByEmailQuery (DTO hydraté de la request)
- FindByEmailHandler (fait le find en appelant l'interface repository du domain)


Domain:
Répertoire dans lequel se trouve les objets métier de notre application (et possiblement les objets de lecture et d'écriture).
Il contient les interfaces de repository.
- On fait un appel du style repositoryInterface->findOneBy et on retourne un objet hydraté
  Le repositoryInterface a son implémentation dans la couche Infrastructure


Infrastructure:
Répertoire dans lequel on va retrouver les appels api, doctrine, aws, etc.
Dedans, le findOneBy va faire une requête doctrine par exemple et retourner l'objet sous forme de tableau (surtout pas une entité)
Les entités doctrine se trouvent dans la couche infra





Write:

UI (peut appeler n'importe quelle couche):
- controller (API REST ou page web ou cli) par exemple. Il appelle la couche Application avec un DTO (par exemple ChangeEmailCommand) qui est hydraté avec la requête POST
- Puis fait un handle de ChangeEmailCommand. Cet appel à handle va appeler $this->commandBus->handle()
- Cet appel à handle va faire appel à MessageBus qui fait un $this->messageBus->dispatch($command) qui appelera la classe ChangeEmailHandler de la couche Application
- Cette couche appelle le template si nécessaire


Application:
Répertoire Command dans lequel se trouve le répertoire du use case (par exemple ChangeEmail) et, dans ce répertoire:
- ChangeEmailCommand (DTO hydraté de la request)
- ChangeEmailHandler va
  -> récupérer le user en rejouant les events via le UserRepositoryInterface de la couche Domain (qui appelle la méthode load de EventSourcingRepository de Broadway)
  -> appeler la méthode changeEmail de la classe User du Domain
  -> stocker l'event (UserEmailChanged) dans la bdd (table events de doctrine)


Domain:
Répertoire dans lequel se trouve les objets métier de notre application (et possiblement les objets de lecture et d'écriture).
Il contient les interfaces de repository.
- On fait un appel du style $user->changeEmail(). Cet appel va déclencher un domainEvent (UserEmailChanged).
- Ce domainEvent est déclenché par la méthode apply (de Broadway).
- Cette méthode va appeler la méthode applyUserEmailChanged dans la classe User du Domain.
- Dans la classe User, la méthode applyUserEmailChanged va mettre à jour l'email et updatedAt
- On stocke l'event (UserEmailChanged) dans la bdd (table events de doctrine) en repassant par la couche Application
- Puis on appelle la méthode applyUserEmailChanged de la classe de projection de l'Infrastructure (UserProjectionFactory)


Infrastructure:
- Répertoire dans lequel on va retrouver les appels api, doctrine, aws, etc.
- Dedans, le UserProjectionFactory va appeler la méthode applyUserEmailChanged, récupérer le user de la bdd par son uuid, mettre à jour l'email et le champ updatedAt et appeler la méthode apply du repository (MysqlReadModelUserRepository) qui fait un flush
  Les entités doctrine se trouvent dans la couche infra