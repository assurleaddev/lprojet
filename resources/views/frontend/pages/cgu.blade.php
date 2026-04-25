@extends('layouts.app')

@section('title', "Conditions Générales d'Utilisation")

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-3xl font-light text-center mb-4">Conditions Générales d'Utilisation<br>du service Used.</h1>

    <div class="space-y-8 text-sm text-gray-600 leading-relaxed mt-10">

        <div class="text-center space-y-4">
            <p>La société USEDIGITAL SARL immatriculée sous le n° 65187 , dont le siège social est AVN MANSOUR DEHBI KENITRA , gère le site <strong>www.used.ma</strong> accessible à l'adresse <strong>www.used.ma</strong> (ci-après le « Site »).</p>
            <p>Le Site met en relation des Vendeurs et des Acheteurs souhaitant vendre des articles, neufs et d'occasion (ci-après les Articles) pour femmes et enfants. Les Vendeurs et les Acheteurs sont définis ci-après par le terme « Utilisateurs ».</p>
            <p>Lorsqu'un utilisateur achète un produit, son règlement est conservé par USED. Cette somme est versée au Vendeur lorsque l'Acheteur confirme la bonne réception et la conformité du produit reçu.</p>
            <p>Les présentes Conditions Générales ont pour objet de définir les conditions d'utilisation du Site par les Utilisateurs.</p>
        </div>

        {{-- Definitions --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-4">Définitions :</h2>
            <ol class="space-y-3 list-none">
                <li><strong>1. Site ou Application ou USED</strong> – désigne le site Web à l'adresse www.used.ma ou l'application mobile USED ;</li>
                <li><strong>2. Utilisateur</strong> – désigne toute personne physique qui, après s'être inscrite sur le Site, est habilitée à utiliser tous les Services du Site, afin de satisfaire ses besoins personnels, non liés à une activité professionnelle ;</li>
                <li><strong>3. Acheteur</strong> – désigne un Utilisateur qui désire acheter ou qui a acheté un ou plusieurs Article(s) ;</li>
                <li><strong>4. Vendeur</strong> – désigne un Utilisateur qui désire vendre ou qui a vendu un ou plusieurs Article(s).</li>
                <li><strong>5. Compte</strong> – désigne le résultat de l'inscription de l'Utilisateur sur le Site, le compte créé contenant des données personnelles, notamment des informations relatives à l'utilisation des Services sur le Site ;</li>
                <li><strong>6. Visiteur</strong> – désigne toute personne physique non inscrite sur le Site susceptible d'utiliser le Site sans avoir à s'inscrire, conformément aux conditions et à la procédure indiquées dans les Conditions Générales d'Utilisation ;</li>
                <li><strong>7. Articles</strong> – désigne les marchandises/articles dont la circulation n'est pas interdite en vertu du droit marocaine applicable, et qui sont déposé(e)s par l'utilisateur sur le Catalogue du Site ;</li>
                <li><strong>8. Dressing</strong> – désigne l'ensemble des articles proposés à la vente sur USED par un même Vendeur ;</li>
                <li><strong>9. Catalogue</strong> – désigne l'ensemble des articles proposés à la vente sur USED ;</li>
                <li><strong>10. Commission</strong> – désigne la somme perçue par USED en contrepartie de l'utilisation du Site par les Utilisateurs ;</li>
                <li><strong>11. Frais de port</strong> – désigne les frais d'expédition fixés par USED sur la base des tarifs des sociétés de livraison partenaires ;</li>
                <li><strong>12. Prix de l'Article</strong> – désigne le prix proposé par le Vendeur pour la vente d'un Article. Le Prix de l'Article constitue l'assiette de la Commission de USED. Le Prix de l'Article comprend la Commission de USED , mais ne comprend pas les Frais de Port ;</li>
                <li><strong>13. Prix de la Transaction</strong> – désigne le prix de l'Article payé par l'Acheteur. Il comprend le Prix de l'Article, augmenté des Frais de Port ;</li>
                <li><strong>14. Adresse de Livraison</strong> – désigne l'adresse postale à laquelle l'Article commandé par un Acheteur lui sera livré par le livreur ;</li>
                <li><strong>15. Services</strong> – désigne la possibilité offerte par USED, aux Utilisateurs et aux Visiteurs, dans la mesure où le présent Contrat permet l'utilisation du Site, notamment, sans que cela soit limitatif, de déposer des Articles sur les Catalogues correspondants, et/ou d'examiner les Articles, de participer directement à l'achat-vente , de trouver les Articles (ou l'Utilisateur les proposant), de communiquer entre eux en public via les commentaires ;</li>
                <li><strong>16. USED protection</strong> – désigne la possibilité pour un Acheteur non satisfait de sa commande de déclarer, dans les 24 heures suivants sa réception (y compris week-ends et jours fériés), son insatisfaction et les raisons de son insatisfaction selon la procédure décrite à l'article 5 des présentes Conditions. Passé ce délai, l'Acheteur est considéré comme satisfait par le produit acheté.</li>
                <li><strong>17. Tiers de confiance</strong> – USED agit comme Tiers de confiance entre l'Acheteur et le Vendeur. Lorsqu'un utilisateur achète un produit, son règlement est conservé par USED. Cette somme est versée au Vendeur lorsque l'Acheteur confirme qu'il a reçu l'article et qu'il en est satisfait. USED garantit ainsi l'Acheteur contre la non réception du colis et le Vendeur contre tout risque d'impayé.</li>
                <li><strong>18. Comptes de Site</strong> – désigne les comptes sur Facebook, Instagram, Google+, Twitter, Pinterest, Youtube et autres réseaux sociaux, où des informations sont fournies à propos du Site et des Services qu'il propose, en partageant les contenus publiés par les Utilisateurs ;</li>
                <li><strong>19. Cookie</strong> – désigne un petit fichier texte contenant des informations stocké sur l'appareil de toute personne qui visite le Site (par exemple, un ordinateur, une tablette ou un mobile) ;</li>
                <li><strong>20. Navigateur</strong> – désigne le programme conçu pour afficher les sites Web (pages Web) sur le Web ou sur un ordinateur personnel ;</li>
                <li><strong>21. Données Personnelles</strong> – désigne les informations relatives à une personne physique, qui est ou peut être identifiée, directement ou indirectement, par référence à un numéro d'identification ou à un ou plusieurs critères qui lui est/sont propre(s) ;</li>
                <li><strong>22. Administrateur du Site</strong> – désigne la personne responsable de l'administration du Site, à savoir USED ;</li>
                <li><strong>23. Adresse IP</strong> – désigne un numéro unique assigné à chaque ordinateur connecté à Internet ; ce numéro est connu comme une adresse de Protocole Internet (IP) ;</li>
                <li><strong>24. Partenaires</strong> – désigne ceux qui aident à fournir les Services aux Utilisateurs ;</li>
            </ol>
        </div>

        {{-- Article 1 --}}
        <div>
            <h2 class="text-base font-bold text-green-600 mb-3">Article 1. Inscription sur le Site</h2>
            <div class="space-y-3">
                <p>Lors de son inscription sur USED, l'utilisateur devra remplir un formulaire contenant son nom, son prénom, son nom de store, son adresse e-mail, son numéro de téléphone et sa date de naissance. Son numéro de téléphone sera confirmé par la réception d'un SMS à des fins de sécurité. L'utilisateur pourra également choisir de s'inscrire via son compte Facebook ou son compte Apple, il sera par la suite invité à compléter ses informations personnelles afin de pouvoir profiter pleinement des services de USED.</p>
                <p>Après l'inscription, le Compte de l'Utilisateur est créé. Un Utilisateur peut modifier, ou supprimer son Compte Utilisateur à tout moment.</p>
                <p>L'utilisateur s'engage, par ailleurs, à actualiser dans les meilleurs délais les informations fournies USED. Il est seul responsable des conséquences éventuelles de l'absence d'actualisation de ces informations.</p>
                <p>L'Utilisateur s'engage à ne communiquer son email et son mot de passe en aucun cas et supportera seul les conséquences de l'utilisation de son compte, même à son insu, par une personne utilisant son compte.</p>
                <p>L'Utilisateur s'engage à ne pas s'enregistrer sous un pseudonyme portant atteinte aux droits d'un tiers, ni à un droit de propriété intellectuelle, à une marque déposée ou à une dénomination sociale. Dans le formulaire, certaines informations sont resteront privées. Le caractère public ou privé des informations à fournir est signalé devant chaque champ à remplir. Le pseudonyme ne doit pas inclure des informations personnelles de l'utilisateur (nom, prénom, numéro de téléphone, etc...)</p>
                <p>l'Utilisateur s'engage à ne pas porter atteinte au respect de la vie privée des autres Utilisateurs et à ne pas leur envoyer de message, d'objet, de documentation notamment publicitaire, par quelque moyen que ce soit (notamment par voie postale, téléphonique ou électronique). En cas de non-respect, le site se réserve le droit déclencher toutes les procédures qu'il jugera utiles.</p>
                <p>L'Utilisateur s'engage à ne pas indiquer ses coordonnées (téléphone, adresse, email…) dans ses Annonces, ni dans les commentaires des articles, ni dans le nom, le pseudo ou la description de son Dressing. USED pourra, dans le cas échant, supprimer ces informations sans en avoir fait la demande préalable auprès de l'Utilisateur. En cas de non respect, le site se réserve le droit déclencher toutes les procédures qu'il jugera utiles, à savoir bannir provisoirement ou définitivement l'utilisateur.</p>
                <p>L'Utilisateur s'engage à ne pas employer sur le Site , des propos contraires aux bonnes mœurs, injurieux, diffamatoires ou pouvant porter atteinte à la réputation des autres Utilisateurs. En cas de non-respect de cette obligation, USED se réserve le droit de suspendre le compte de l'Utilisateur sans notification préalable. En cas de récidive, USED se réserve le droit de clôturer définitivement le compte de l'Utilisateur et de déclencher toutes les procédures qu'il jugera utiles.publiques et d'autres</p>
            </div>
        </div>

        {{-- Article 2 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 2 : Confidentialité</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.1 Dispositions générales</h3>
                    <p>Seuls les résidents au Maroc ont le droit d'utiliser le site et l'application. USED s'engage à recueillir et utiliser les Données Personnelles des Visiteurs ou des Utilisateurs dans le strict respect de la loi marocaine sur la protection des données.</p>
                    <p class="mt-2">En utilisant le Site, Utilisateurs et Visiteurs acceptent que leurs Données Personnelles soient traitées de la manière et aux fins indiquées dans les présentes. Les Utilisateurs sont présumés avoir lu et compris la présente Politique de Confidentialité, après l'avoir acceptée lors de leur inscription. La Politique de Confidentialité est disponible sur le Site à tout moment, et peut également être téléchargée et imprimée à partir de ce même Site. Les Utilisateurs seront toujours informés en avance de la mise en œuvre des futurs changements et/ou compléments à la présente Politique de Confidentialité, qui seront envoyés par courriel et publiés sur le Site. Lorsque les Utilisateurs commandent des Services et/ou se connectent sur le Site après ces modifications, leur attention sera particulièrement attirée sur l'existence de ces changements. La présente Politique de Confidentialité s'applique de plein droit aux Visiteurs qui utilisent le Site.</p>
                    <p class="mt-2">Les Utilisateurs et/ou les Visiteurs doivent noter que le Site est susceptible de contenir des liens vers d'autres sites Web de personnes, de sociétés ou d'organismes, et que USED n'est pas responsable du contenu de ces sites ni de leurs politiques de confidentialité. Ces tiers peuvent recueillir les Données Personnelles des Consommateurs et/ou des Visiteurs lorsqu'ils consultent leurs publicités et/ou cliquent dessus. En outre, avant de soumettre des informations les concernant, les Utilisateurs et/ou les Visiteurs sont invités à examiner les règlements, la politique de confidentialité et les autres documents des sites Web en question.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.2 Recueil et stockage des données personnelles</h3>
                    <p>USED recueille, stocke et utilise les Données Personnelles suivantes des Utilisateurs :</p>
                    <p class="mt-1">- Au moment de l'inscription ou de la modification du compte: nom, prénom, numéro de téléphone, ville et adresse électronique.<br>- Au moment de l'utilisation des Services : Article référencé , Données de localisation des Utilisateurs.</p>
                    <p class="mt-2">USED s'autorise à recueillir, stocker et utiliser les données de localisation des Utilisateurs en vue de simplifier leur mise en relation permettre d'identifier les Utilisateurs les plus proches et favoriser les Transactions entre Acheteur et Vendeur. L'adresse exacte de localisation ne sera jamais divulguée, et seule une information approximative sera utilisée. Ces données pourront être collectées par différentes technologies et notamment, sans que ce soit limitatif.</p>
                    <p class="mt-2">USED recueille, stocke et utilise les Données Personnelles des Visiteurs en se basant sur des cookies, comme indiqué ci-dessous.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.3 Objectifs de l'utilisation des données personnelles</h3>
                    <p>USED utilise les Données Personnelles aux fins suivantes :</p>
                    <ul class="mt-2 space-y-2 list-none">
                        <li>2.3.1 Pour fournir les Services à un Utilisateur et/ou un Visiteur. Afin d'utiliser certains Services proposés sur le Site, un Utilisateur doit fournir des Données Personnelles indispensables pour pouvoir utiliser des Services spécifiques ;</li>
                        <li>2.3.2 A des fins d'authentification ;</li>
                        <li>2.3.3 Pour fournir les Services de manière efficace. USED veille à ce que l'utilisation du Site et de ses Services soit efficace. USED s'efforce en outre d'éviter les usurpations d'identité ;</li>
                        <li>2.3.4 Pour assurer l'amélioration et le développement continus des Services, et pour fournir à un Utilisateur et/ou un Visiteur les offres de Service les mieux adaptées à ses besoins ;</li>
                        <li>2.3.5 Pour respecter les règlements (lesquels peuvent inclure des obligations légales de conservation des données) ;</li>
                        <li>2.3.6 Un Utilisateur et/ou un Visiteur peut s'opposer à tout moment et pour des raisons légitimes au traitement des données décrit ci-dessus en envoyant un courriel à l'Administrateur du Site. En fonction de la nature du traitement faisant l'objet de l'opposition, l'Utilisateur ou le Visiteur peut se voir de ce fait empêché d'utiliser tout ou partie des Services.</li>
                        <li>2.3.7 Dès lors que l'utilisation des Données Personnelles requiert l'accord préalable de l'Utilisateur, il convient d'obtenir son accord.</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.4 Droit d'accès aux données personnelles et modification</h3>
                    <p>Les Utilisateurs et les Visiteurs peuvent exercer leur droit d'accès aux Données Personnelles, de modification ou de suppression de celles-ci.</p>
                    <p class="mt-2">Afin de mettre en œuvre les droits susmentionnés, les Utilisateurs peuvent modifier, désactiver temporairement ou supprimer leurs comptes dans les réglages en cliquant sur 'Parmaètres' puis 'Mon compte'.<br>En cas de suppression définitive de compte, toutes les commandes en cours seront annulées et le compte de l'Utilisateur désactivé. L'Utilisateur dispose d'un droit de rétractation de quinze (15) jours à partir de la date de fin de traitement des commandes en cours. Si pendant cette période, l'Utilisateur décide de récupérer son compte, il peut le faire en envoyant un e-mail à <a href="mailto:contact@used.ma" class="text-gray-900 underline">contact@used.ma</a>.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.5 Protection des données personnelles</h3>
                    <p>USED garantit que les Données Personnelles fournies par un Utilisateur et/ou un Visiteur sont protégées contre toute activité illégale, telle que l'utilisation abusive des Données Personnelles, la modification ou la destruction des Données Personnelles, l'usurpation d'identité, la fraude. La divulgation de l'identité d'un Utilisateur à un autre n'est dévoilé qu'en cas de procédure entre ces derniers.</p>
                    <p class="mt-2">Les Utilisateurs et les Visiteurs s'engagent à ne pas divulguer leurs Données Personnelles, ni les Données Personnelles d'un tiers si celles-ci ont été mises à leur disposition, et doivent informer USED immédiatement en cas de violations.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.6 Cookies</h3>
                    <p>Le Site utilise des cookies pour distinguer les Utilisateurs/Visiteurs des autres utilisateurs du Site afin d'aider USED à améliorer leur visite lorsqu'ils naviguent sur le Site.</p>
                </div>
            </div>
        </div>

        {{-- Article 3 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 3. Vendre sur USED</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.1 Articles mis en vente</h3>
                    <div class="space-y-2">
                        <p>Le Vendeur s'engage à ce que tout Article mis en vente corresponde à un bien matériel disponible immédiatement dont il dispose de la pleine et entière propriété et capacité de vente.</p>
                        <p>Le Vendeur s'engage à ce que les Articles ne comportent pas de défauts : tache visible, article troué ou déchiré, etc. L'Acheteur ayant reçu un Article dans cet état, dispose d'un droit de retour de ce dernier.</p>
                        <p>Le Vendeur s'engage à emballer correctement son article, spécialement les articles qui risquent d'être abîmés lors du transport. USED ainsi que ses partenaires de livraison ne sont pas tenus responsables de tout sinistre survenu lors de l'acheminement d'un article vers son destinataire. Il incombe au Vendeur de veiller à bien emballer son article (en utilisant du papier-bulle ou autre) afin de limiter le risque de casse.</p>
                        <p>Le Vendeur s'engage à ce que tout article de beauté (maquillage et soins) mis en vente corresponde à un produit non ouvert dans son emballage d'origine.</p>
                        <p>Les articles de lingerie, maillots de bain, chaussettes et collants ainsi que les vêtements de nuit mis en vente sur USED doivent être neufs (avec ou sans étiquette).</p>
                        <p>Le Vendeur s'engage à fournir une photo réelle de son Article mis en vente.</p>
                        <p>Le Vendeur s'engage à décrire l'Article sur le Site. La description de l'Article doit correspondre à ses caractéristiques effectives. Le Vendeur est le seul responsable de la description des articles.</p>
                        <p>USED se réserve le droit de solliciter au Vendeur qu'il modifie et/ou supprime son Annonce et/ou de le faire directement, notamment en cas d'erreur concernant la catégorie de l'article, ou en cas de renseignement des coordonnées dans l'annonce, ou en cas d'article interdit de vente sur le site.</p>
                        <p>Le Vendeur détermine le Prix de vente de l'Article. Le prix d'achat en boutique n'est pas obligatoire. Si le Vendeur ne connaît pas le prix d'achat, il peut se contenter de mettre une estimation.</p>
                        <p>USED s'engage à diffuser l'Annonce du Vendeur sur le Site sans limitation de durée, sous réserve du respect par le Vendeur des présentes Conditions Générales. USED rend indisponible l'Annonce du Vendeur immédiatement après l'émission par un Acheteur d'une Offre d'Achat.</p>
                        <p>Le Vendeur accepte que son Article soit diffusé sur les sites et les supports partenaires de USED.</p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.2 Déroulement d'une transaction (Vente / Achat)</h3>
                    <div class="space-y-2">
                        <p>Lorsque l'Acheteur paye un article en ligne sur le site ou passe une commande pour un paiement à la livraison, USED notifie le Vendeur par email et sur le Site.</p>
                        <p>Le Vendeur s'engage à confier le colis au livreur qui passera chez lui.</p>
                        <p>Les Offres d'Achat émises par l'Acheteur sont indépendantes les unes des autres.</p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.3 Paiement de la commande</h3>
                    <div class="space-y-2">
                        <p>Les paiements en ligne sont assurés par la plateforme de paiement CMI.<br>CMI est un service sécurisé de gestion des transactions.</p>
                        <p>L'Acheteur peut payer :<br>- En espèces à la livraison<br>- Par carte bancaire</p>
                        <p>En cas de paiement par carte bancaire, les dispositions relatives à l'utilisation frauduleuse du moyen de paiement prévues dans les conventions conclues entre le Consommateur et l'émetteur de la carte entre la Société USEDIGITAL SARL et son établissement bancaire s'appliquent.</p>
                        <p>Les données enregistrées par CMI pour le compte de USEDIGITAL SARL constituent la preuve de l'ensemble des transactions commerciales passées entre vous et la société USEDIGITAL SARL. La collecte des informations de l'Acheteur de carte bancaire est assurée par CMI . La sauvegarde de ces informations se fait en tout état de cause avec le consentement de l'Acheteur, sur le serveur de CMI. En aucun cas, USED ne stocke ou n'a accès aux coordonnées bancaires des Acheteurs.</p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.4 Livraison de l'Article</h3>
                    <div class="space-y-2">
                        <p>Le Vendeur s'engage à bien emballer l'Article, à écrire de façon claire et apparente le numéro de livraison communiqué par USED sur l'emballage et le confier au livreur qui se présentera à son domicile.</p>
                        <p>Le livreur remet une décharge au Vendeur. Le Vendeur doit conserver ce document tout au long du mois suivant la date d'expédition. Sans décharge, le Vendeur ne peut faire valoir ses droits auprès du partenaire de livraison ou de USED.</p>
                        <p>Le Vendeur s'engage à n'insérer aucune publicité pour lui-même ou pour quiconque, sous quelque forme que ce soit.</p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.5. Réception du colis</h3>
                    <p>L'Acheteur s'engage à répondre aux appels du livreur pour confirmer le rendez-vous de passage et le resepcter ou réceptionner son article . Faute de quoi, l'article sera réexpédié au Vendeur. Dans ce cas, USED se réserve le droit de bannir l'Acheteur du site.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.6 Paiement du Vendeur</h3>
                    <div class="space-y-2">
                        <p>Le Vendeur autorise USED à percevoir, en son nom et pour son compte, le Prix de la Transaction par le biais du système sécurisé de paiement électronique mis en place sur le Site ou par le biais du paiement à la livraison.</p>
                        <p>Le Vendeur autorise USED, à retenir sur le Prix total de la Transaction une Commission déterminée ci-après.<br>La Commission facturée au Vendeur par USED est de :<br>• 20% du prix de l'article.</p>
                        <p>Les frais de port ne sont pas inclus dans la base de calcul de la Commission. Une fois, la transaction finalisée, USED versera au Vendeur le prix de l'Article diminué de la Commission, et ce, dès confirmation de la réception de l'Article par l'Acheteur sur le Site, sauf en cas de retour de l'Article par l'Acheteur dans les conditions et délais prévus. ( cf article 4 )</p>
                        <p>Pour recevoir son argent , le Vendeur doit fournir des justificatifs de son identité et de ses coordonnées bancaires accompagnés de leur photos pour faire les vérifications nécessaires:<br>- la CIN ( carte d'identité nationale )<br>- le RIB ( relevé d'identité bancaire à son nom )</p>
                        <p>USED peut suspendre le virement dans l'attente de la réception de ces documents et de leur étude.</p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.7 Retour suite à un échec de livraison</h3>
                    <p>En cas d'échec de livraison suite à l'in joignabilité de l'Acheteur, le Vendeur doit se montrer disponible pour réceptionner son article retourné. Le livreur effectue les tentatives nécessaires pour retourner l'article. À défaut, l'article sera envoyé aux locaux de USED. Le Vendeur dispose dès lors d'un délai de 21 jours pour réclamer son article. Dans le cas échéant, le Vendeur payera les frais de retour à son adresse. Passé ce délai, l'article devient la propriété de USED.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.8 Used Luxury</h3>
                    <div class="space-y-2">
                        <p>Tout article de marque dont le prix est égal ou supérieur à 900 DHS est considéré comme étant un article de luxe et est soumis à une vérification d'authenticité et de conformité de la part de USED avant d'être acheminé vers l'Acheteur.</p>
                        <p>Pour tout Article de luxe vendu sur USED, le Vendeur s'engage à fournir une preuve d'authenticité de l'article en question.</p>
                        <p>Suite à la validation de la commande par USED, un livreur sera envoyé pour récupérer l'article et le livrer dans les locaux de USED. L'Article reçu par USED sera soumis à une expertise afin de valider son authenticité et sa conformité. Cette étape peut nécessiter un délai supplémentaire. L'Acheteur s'engage à accepter ce délai et se montrer disponible lorsque le livreur la contactera pour la livraison. Si l'Article s'avère conforme et authentique, il sera envoyé à l'Acheteur. Dans le cas contraire, l'article est renvoyé au Vendeur moyennant une pénalité de 50 DH.</p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.9 Signaler un article ou un dressing</h3>
                    <p>Si vous remarquez un contenu inapproprié ou ne respectant pas les règles imposées par USED, vous avez la possibilité de le signaler en cliquant sur le bouton 'signaler' ou envoyer un e-mail à l'adresse <a href="mailto:contact@used.ma" class="text-gray-900 underline">contact@used.ma</a>.</p>
                </div>
            </div>
        </div>

        {{-- Article 4 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 4. Garantie 'USED Protection'</h2>
            <p class="mb-4">USED offre cette protection à chaque fois qu'une transaction est réalisée sur le Site, au cas où l'Acheteur constate un défaut ou une non-conformité de l'Article acheté avec la description sur le Site.</p>

            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">4.1 Le retour et le remboursement sur USED</h3>
                    <div class="space-y-2">
                        <p>Quand un achat est effectué sur USED, l'argent n'est versé au Vendeur qu'après 24h de la réception de l'Article par l'Acheteur. l'Acheteur dispose de 24h après la livraison, pour signaler à USED tout problème avec sa commande. L'Acheteur peut effectuer la réclamation par e-mail en envoyant des photos expliquant la nature du problème. L'équipe de USED vérifie la réclamation, et en cas de validation du retour, enverra un livreur pour récupérer l'article et assurer le retour vers le Vendeur. Une fois la réclamation validée, l'Acheteur doit être disponible immédiatement quand le livreur le contactera pour venir récupérer l'article en question. Faute de quoi, l'article ne sera pas réexpédié au Vendeur et l'Acheteur devient propriétaire de l'article et accepte que USED ne lui rembourse pas le prix de l'Article. Si l'Acheteur est disponible et expédie l'Article dans les temps, USED s'engage à rembourser l'Acheteur de la totalité du montant payé par celui-ci, frais de livraison compris, dans un délai de dix (10) jours ouvrables suivant la réception de l'Article par le Vendeur.</p>
                        <p>Le remboursement se fera par virement bancaire.<br>L'Acheteur doit fournir des justificatifs de son identité :<br>- la CIN (carte d'identité nationale)<br>- le RIB (relevé d'identité bancaire)</p>
                        <p>Les frais de retour des articles non conformes, sont à la charge du Vendeur, il s'agit du montant des frais de livraison habituel indiqué sur USED.</p>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">4.2 Ce qui est couvert par la protection</h3>
                    <p>Si l'un des éléments suivants se rapporte à la commande, la demande du retour sera acceptée par USED :</p>
                    <ul class="mt-2 space-y-1 list-none">
                        <li>- Dommages non divulgués</li>
                        <li>- Article incorrect ou manquant</li>
                        <li>- L'article ne correspond pas à la description</li>
                        <li>- L'article n'est pas authentique</li>
                    </ul>
                    <p class="mt-2">Si aucune réclamation n'est faite dans les 24h suivants la livraison, le paiement du Vendeur sera automatiquement effectué. Une fois le virement effectué, toutes les ventes sont finalisées et aucun remboursement ne sera fait.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">4.3 Ce qui n'est pas couvert par la protection</h3>
                    <p>Ce qui n'est pas couvert :</p>
                    <ul class="mt-2 space-y-2 list-none">
                        <li>- Les parfums, les articles de beauté, la lingerie, les maillots de bain, chaussettes et collants</li>
                        <li>- Les articles interdits: téléphones, tablettes et objets de valeur</li>
                        <li>
                            <p>- L'article ne convient pas ou changement d'avis</p>
                            <p class="text-gray-500 text-xs mt-1">Si l'article n'est tout simplement pas au gout de l'Acheteur ou ne lui convient pas, USED ne peut malheureusement pas accepter un retour. L'Acheteur peut à son tour le remettre en vente sur USED.</p>
                        </li>
                        <li>
                            <p>- Les opérations et les transactions hors ligne</p>
                            <p class="text-gray-500 text-xs mt-1">USED protection ne couvre que les opérations effectuées sur la plate-forme. Lorsque le paiement de la valeur totale des articles n'est pas échangé via la plate-forme, USED ne peut pas garantir que les articles soient envoyés ou conformes. l'Acheteur assumera tous les risques associés à ces transactions.</p>
                        </li>
                    </ul>
                    <p class="mt-3">Lorsqu'un article est retourné dans le cadre de la Garantie 'Used protection', USED dispose d'un délai de 5 jours pour traiter la réclamation.</p>
                </div>
            </div>
        </div>

        {{-- Article 5 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 5. Responsabilités</h2>
            <div class="space-y-2">
                <p>USED ne peut être tenu pour responsable ni du contenu des Annonces, ni des actions (ou absence d'action) des Utilisateurs, ni des Articles mis en vente.</p>
                <p>USED ne peut être tenu responsable du caractère diffamatoire, injurieux ou contraire aux bonnes mœurs des commentaires mis en ligne par un Utilisateur.</p>
                <p>USED n'intervient pas dans la transaction entre Acheteurs et Vendeurs. En conséquence, USED n'exerce aucun contrôle sur la qualité des Articles répertoriés, l'exactitude du contenu des annonces des utilisateurs.</p>
                <p>USED ne transfère pas la propriété légale des objets du Vendeur à l'Acheteur. Les accords de vente/achat sont conclus directement entre l'Acheteur et le Vendeur.</p>
                <p>USED se réserve le droit de suspendre, sans préavis ni indemnité et sans engager sa responsabilité, l'accès au Site, temporairement ou définitivement. Il ne garantit pas que le Site sera accessible sans interruption. Il pourra interrompre l'accès au Site pour des raisons notamment de maintenance et en cas d'urgence.</p>
                <p>USED n'est pas responsable de l'utilisation frauduleuse par un tiers et des conséquences éventuelles de l'identifiant et/ou du mot de passe de l'Utilisateur.</p>
                <p>Des liens hypertextes sur le Site peuvent renvoyer à d'autres sites. USED n'est pas responsable du contenu ou des agissements de ces sites.</p>
                <p>Le contrat créé lors de la validation de l'Offre d'Achat de l'Acheteur par le Vendeur lie uniquement le Vendeur et l'Acheteur. USED jouant seulement un rôle d'intermédiaire et ne saurait être tenue responsable des litiges déclarés en dehors du délai de 24 heures suivant la réception de l'Article par l'Acheteur.</p>
                <p>Lorsqu'un litige survient entre les parties, USED pourra, pendant un délai de 30 jours, faire son possible pour aider les parties à trouver un accord.</p>
            </div>
        </div>

        {{-- Article 6 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 6. Dispositions diverses</h2>
            <div class="space-y-3">
                <p>Les présentes Conditions Générales sont valables à compter du 26 AVRIL 2023.</p>
                <p>Dans l'hypothèse où l'une ou plusieurs des stipulations des présentes Conditions Générales serai(en)t écartée(s) par une disposition législative ou réglementaire ou par une décision de justice, toutes les autres dispositions demeureraient applicables.</p>
                <p>En s'inscrivant sur le Site, l'Utilisateur reconnaît avoir pris connaissance, compris et accepté sans réserve l'intégralité des présentes Conditions Générales qui régissent avec force obligatoire l'ensemble des relations entre USED, les Vendeurs et les Acheteurs. Cette acceptation prend effet à compter de la date d'inscription sur le Site de l'Utilisateur et vaut pour chaque opération, Annonce, Offre d'Achat et Vente. Les présentes Conditions Générales prévalent sur tout document contractuel ou non contractuel propre au Vendeur ou propre à l'Acheteur.</p>
                <p>USED a la possibilité de modifier les présentes Conditions Générales en fonction de l'évolution de son offre et du marché. L'Utilisateur s'engage donc à consulter régulièrement les Conditions Générales du Site pour prendre connaissance des modifications et ayant été apportées. Tout refus d'acceptation des Conditions modifiées devra être notifié en s'adressant au Service Client en envoyant un e-mail à l'adresse <a href="mailto:contact@used.ma" class="text-gray-900 underline">contact@used.ma</a>.</p>
                <p>Le présent contrat est conclu entre USED et l'Utilisateur pour une durée indéterminée. Il est résiliable par simple notification à tout moment et sans motif particulier, par l'une ou l'autre des parties en présence.</p>
                <p>Le Site, son contenu, son catalogue, ses textes, ses illustrations, ses photographies et ses images sont la propriété de Used. Il est interdit de reproduire, représenter et/ou exploiter tout ou partie du Site, de son contenu, de son catalogue, de ses textes, ses illustrations, de ses photographies et de ses images sans autorisation préalable de Used.</p>
                <p>Pour toutes questions concernant ces conditions générales d'utilisation ou le Site, contactez USED à l'adresse suivante : <a href="mailto:contact@used.ma" class="text-gray-900 underline">contact@used.ma</a>.</p>
            </div>
        </div>

    </div>
</div>
@endsection
