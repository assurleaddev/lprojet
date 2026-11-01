@extends('layouts.app')

@section('title', "Conditions Générales d'Utilisation")

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-3xl font-light text-center mb-4">Conditions Générales d'Utilisation<br>du service Usedis.</h1>
    <p class="text-center text-sm text-gray-500">Le cadre de confiance : les règles qui encadrent l'utilisation de la plateforme Usedis, pour les acheteurs comme pour les vendeurs.<br>Édition du 26 avril 2023.</p>

    <div class="space-y-8 text-sm text-gray-600 leading-relaxed mt-10">

        {{-- Préambule --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Préambule</h2>
            <div class="space-y-3">
                <p>La société USEDIGITAL SARL immatriculée sous le n° 65187, dont le siège social est AVN MANSOUR DEHBI KENITRA, gère le site Usedis.com accessible à l'adresse <strong>www.usedis.com</strong> (ci-après le « Site »).</p>
                <p>Le Site met en relation des Vendeurs et des Acheteurs souhaitant vendre des articles, neufs et d'occasion (ci-après les Articles) pour femmes et enfants. Les Vendeurs et les Acheteurs sont définis ci-après par le terme « Utilisateurs ».</p>
                <p>Lorsqu'un utilisateur achète un produit, son règlement est conservé par Usedis. Cette somme est versée au Vendeur lorsque l'Acheteur confirme la bonne réception et la conformité du produit reçu.</p>
                <p>Les présentes Conditions Générales ont pour objet de définir les conditions d'utilisation du Site par les Utilisateurs.</p>
            </div>
        </div>

        {{-- Définitions --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-4">Définitions</h2>
            <ol class="space-y-3 list-none">
                <li><strong>01. Site ou Application ou Usedis</strong> – désigne le site Web à l'adresse www.usedis.com ou l'application mobile Usedis ;</li>
                <li><strong>02. Utilisateur</strong> – désigne toute personne physique qui, après s'être inscrite sur le Site, est habilitée à utiliser tous les Services du Site, afin de satisfaire ses besoins personnels, non liés à une activité professionnelle ;</li>
                <li><strong>03. Acheteur</strong> – désigne un Utilisateur qui désire acheter ou qui a acheté un ou plusieurs Article(s) ;</li>
                <li><strong>04. Vendeur</strong> – désigne un Utilisateur qui désire vendre ou qui a vendu un ou plusieurs Article(s).</li>
                <li><strong>05. Compte</strong> – désigne le résultat de l'inscription de l'Utilisateur sur le Site, le compte créé contenant des données personnelles, notamment des informations relatives à l'utilisation des Services sur le Site ;</li>
                <li><strong>06. Visiteur</strong> – désigne toute personne physique non inscrite sur le Site susceptible d'utiliser le Site sans avoir à s'inscrire ;</li>
                <li><strong>07. Articles</strong> – désigne les marchandises/articles dont la circulation n'est pas interdite en vertu du droit marocain applicable, et qui sont déposé(e)s par l'utilisateur sur le Catalogue du Site ;</li>
                <li><strong>08. Dressing</strong> – désigne l'ensemble des articles proposés à la vente sur Usedis par un même Vendeur ;</li>
                <li><strong>09. Catalogue</strong> – désigne l'ensemble des articles proposés à la vente sur Usedis ;</li>
                <li><strong>10. Commission</strong> – désigne la somme perçue par Usedis en contrepartie de l'utilisation du Site par les Utilisateurs ;</li>
                <li><strong>11. Frais de port</strong> – désigne les frais d'expédition fixés par Usedis sur la base des tarifs des sociétés de livraison partenaires ;</li>
                <li><strong>12. Prix de l'Article</strong> – désigne le prix proposé par le Vendeur pour la vente d'un Article. Le Prix de l'Article constitue l'assiette de la Commission de Usedis ;</li>
                <li><strong>13. Prix de la Transaction</strong> – désigne le prix de l'Article payé par l'Acheteur. Il comprend le Prix de l'Article, augmenté des Frais de Port ;</li>
                <li><strong>14. Adresse de Livraison</strong> – désigne l'adresse postale à laquelle l'Article commandé par un Acheteur lui sera livré ;</li>
                <li><strong>15. Services</strong> – désigne la possibilité offerte par Usedis aux Utilisateurs et aux Visiteurs d'utiliser le Site ;</li>
                <li><strong>16. Usedis Protection</strong> – désigne la possibilité pour un Acheteur non satisfait de sa commande de déclarer, dans les 24 heures suivant sa réception, son insatisfaction selon la procédure décrite à l'article 5 des présentes Conditions ;</li>
                <li><strong>17. Tiers de confiance</strong> – Usedis agit comme Tiers de confiance entre l'Acheteur et le Vendeur, garantissant l'Acheteur contre la non-réception du colis et le Vendeur contre tout risque d'impayé ;</li>
                <li><strong>18. Comptes de Site</strong> – désigne les comptes sur les réseaux sociaux où des informations sont fournies à propos du Site ;</li>
                <li><strong>19. Cookie</strong> – désigne un petit fichier texte contenant des informations stocké sur l'appareil de toute personne qui visite le Site ;</li>
                <li><strong>20. Données Personnelles</strong> – désigne les informations relatives à une personne physique identifiable ;</li>
                <li><strong>21. Administrateur du Site</strong> – désigne la personne responsable de l'administration du Site, à savoir Usedis ;</li>
                <li><strong>22. Adresse IP</strong> – désigne un numéro unique assigné à chaque ordinateur connecté à Internet ;</li>
                <li><strong>23. Partenaires</strong> – désigne ceux qui aident à fournir les Services aux Utilisateurs.</li>
            </ol>
        </div>

        {{-- Article 1 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 1 / Inscription sur le Site</h2>
            <div class="space-y-3">
                <p>Lors de son inscription sur Usedis, l'utilisateur devra remplir un formulaire contenant son nom, son prénom, son nom de store, son adresse e-mail, son numéro de téléphone et sa date de naissance. Son numéro de téléphone sera confirmé par la réception d'un SMS à des fins de sécurité. L'utilisateur pourra également choisir de s'inscrire via son compte Facebook ou son compte Apple.</p>
                <p>Après l'inscription, le Compte de l'Utilisateur est créé. Un Utilisateur peut modifier ou supprimer son Compte Utilisateur à tout moment.</p>
                <p>L'utilisateur s'engage, par ailleurs, à actualiser dans les meilleurs délais les informations fournies à Usedis. Il est seul responsable des conséquences éventuelles de l'absence d'actualisation de ces informations.</p>
                <p>L'Utilisateur s'engage notamment à :</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Ne pas communiquer son email et son mot de passe à des tiers ;</li>
                    <li>Ne pas s'enregistrer sous un pseudonyme portant atteinte aux droits d'un tiers ;</li>
                    <li>Ne pas porter atteinte au respect de la vie privée des autres Utilisateurs ;</li>
                    <li>Ne pas indiquer ses coordonnées dans ses Annonces ou commentaires ;</li>
                    <li>Ne pas employer des propos contraires aux bonnes mœurs ou diffamatoires.</li>
                </ul>
                <p>En cas de non-respect de ces obligations, Usedis se réserve le droit de suspendre ou clôturer définitivement le compte de l'Utilisateur sans notification préalable.</p>
            </div>
        </div>

        {{-- Article 2 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 2 / Confidentialité</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.1 Dispositions générales</h3>
                    <p>Seuls les résidents au Maroc ont le droit d'utiliser le site et l'application. Usedis s'engage à recueillir et utiliser les Données Personnelles des Visiteurs ou des Utilisateurs dans le strict respect de la loi marocaine sur la protection des données.</p>
                    <p class="mt-2">En utilisant le Site, Utilisateurs et Visiteurs acceptent que leurs Données Personnelles soient traitées de la manière et aux fins indiquées dans les présentes. La <a href="{{ route('privacy') }}" class="text-gray-900 underline">Politique de Confidentialité</a> est disponible sur le Site à tout moment.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.2 Recueil et stockage des données personnelles</h3>
                    <p>Usedis recueille, stocke et utilise les Données Personnelles suivantes des Utilisateurs :</p>
                    <ul class="list-disc pl-5 space-y-1 mt-1">
                        <li>Au moment de l'inscription : nom, prénom, numéro de téléphone, ville et adresse électronique ;</li>
                        <li>Au moment de l'utilisation des Services : articles référencés, données de localisation des Utilisateurs.</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.3 Objectifs de l'utilisation des données personnelles</h3>
                    <p>Usedis utilise les Données Personnelles aux fins suivantes : fournir les Services, assurer l'authentification, améliorer et développer les Services, respecter les règlements en vigueur. Un Utilisateur peut s'opposer à tout moment au traitement de ses données en envoyant un courriel à l'Administrateur du Site.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.4 Droit d'accès aux données personnelles et modification</h3>
                    <p>Les Utilisateurs peuvent exercer leur droit d'accès, de modification ou de suppression de leurs Données Personnelles via les paramètres de leur compte. En cas de suppression définitive, toutes les commandes en cours seront annulées. L'Utilisateur dispose d'un droit de rétractation de quinze (15) jours. Pour récupérer son compte pendant cette période, il peut envoyer un e-mail à <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a>.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.5 Protection des données personnelles</h3>
                    <p>Usedis garantit que les Données Personnelles fournies par un Utilisateur sont protégées contre toute activité illégale, telle que l'utilisation abusive, la modification ou la destruction des données, l'usurpation d'identité ou la fraude.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">2.6 Cookies</h3>
                    <p>Le Site utilise des cookies pour distinguer les Utilisateurs des autres visiteurs afin d'améliorer leur expérience de navigation.</p>
                </div>
            </div>
        </div>

        {{-- Article 3 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 3 / Vendre sur Usedis</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.1 Articles mis en vente</h3>
                    <p>Le Vendeur s'engage à ce que tout Article mis en vente corresponde à un bien matériel disponible immédiatement dont il dispose de la pleine et entière propriété. Le Vendeur s'engage à ce que les Articles ne comportent pas de défauts visibles et à emballer correctement son article. Usedis ainsi que ses partenaires de livraison ne sont pas tenus responsables de tout sinistre survenu lors de l'acheminement.</p>
                    <p class="mt-2">Les articles de lingerie, maillots de bain, chaussettes et collants ainsi que les vêtements de nuit mis en vente sur Usedis doivent être neufs (avec ou sans étiquette). Le Vendeur s'engage à fournir une photo réelle et une description conforme de son Article.</p>
                    <p class="mt-2">Usedis se réserve le droit de solliciter au Vendeur qu'il modifie et/ou supprime son Annonce, notamment en cas d'erreur concernant la catégorie de l'article ou en cas de renseignement des coordonnées dans l'annonce.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.2 Déroulement d'une transaction</h3>
                    <p>Lorsque l'Acheteur paye un article en ligne sur le site ou passe une commande pour un paiement à la livraison, Usedis notifie le Vendeur par email et sur le Site. Le Vendeur s'engage à confier le colis au livreur qui passera chez lui.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.3 Paiement de la commande</h3>
                    <p>Les paiements en ligne sont assurés par la plateforme de paiement CMI. L'Acheteur peut payer en espèces à la livraison ou par carte bancaire. En aucun cas, Usedis ne stocke ou n'a accès aux coordonnées bancaires des Acheteurs.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.4 Livraison de l'Article</h3>
                    <p>Le Vendeur s'engage à bien emballer l'Article, à écrire de façon claire le numéro de livraison communiqué par Usedis sur l'emballage et à le confier au livreur. Le Vendeur doit conserver la décharge remise par le livreur tout au long du mois suivant la date d'expédition.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.5 Réception du colis</h3>
                    <p>L'Acheteur s'engage à répondre aux appels du livreur pour confirmer le rendez-vous de passage et réceptionner son article. Faute de quoi, l'article sera réexpédié au Vendeur et Usedis se réserve le droit de bannir l'Acheteur du site.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.6 Paiement du Vendeur</h3>
                    <p>Le Vendeur autorise Usedis à percevoir, en son nom et pour son compte, le Prix de la Transaction. La Commission facturée au Vendeur par Usedis est de :</p>
                    <ul class="list-disc pl-5 mt-1">
                        <li>20% du prix de l'article</li>
                    </ul>
                    <p class="mt-2">Les frais de port ne sont pas inclus dans la base de calcul de la Commission. Une fois la transaction finalisée, Usedis versera au Vendeur le prix de l'Article diminué de la Commission, dès confirmation de la réception de l'Article par l'Acheteur.</p>
                    <p class="mt-2">Pour recevoir son argent, le Vendeur doit fournir : sa CIN (carte d'identité nationale) et son RIB (relevé d'identité bancaire).</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.7 Retour suite à un échec de livraison</h3>
                    <p>En cas d'échec de livraison suite à l'injoignabilité de l'Acheteur, le Vendeur dispose d'un délai de 21 jours pour réclamer son article. Passé ce délai, l'article devient la propriété de Usedis.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.8 Usedis Luxury</h3>
                    <p>Tout article de marque dont le prix est égal ou supérieur à 900 DHS est considéré comme un article de luxe et est soumis à une vérification d'authenticité de la part de Usedis avant d'être acheminé vers l'Acheteur. Si l'Article s'avère non conforme, il est renvoyé au Vendeur moyennant une pénalité de 50 DH.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">3.9 Signaler un article ou un dressing</h3>
                    <p>Si vous remarquez un contenu inapproprié, vous avez la possibilité de le signaler en cliquant sur le bouton 'Signaler' ou en envoyant un e-mail à <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a>.</p>
                </div>
            </div>
        </div>

        {{-- Article 4 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 4 / Garantie « Usedis Protection »</h2>
            <div class="space-y-4">
                <p>Usedis offre cette protection à chaque fois qu'une transaction est réalisée sur le Site, au cas où l'Acheteur constate un défaut ou une non-conformité de l'Article acheté avec la description sur le Site.</p>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">4.1 Le retour et le remboursement sur Usedis</h3>
                    <p>L'Acheteur dispose de 24h après la livraison pour signaler à Usedis tout problème avec sa commande par e-mail avec des photos. Si la réclamation est validée, Usedis s'engage à rembourser l'Acheteur de la totalité du montant payé, frais de livraison compris, dans un délai de dix (10) jours ouvrables. Le remboursement se fera par virement bancaire sur présentation de la CIN et du RIB de l'Acheteur.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">4.2 Ce qui est couvert par la protection</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Dommages non divulgués ;</li>
                        <li>Article incorrect ou manquant ;</li>
                        <li>L'article ne correspond pas à la description ;</li>
                        <li>L'article n'est pas authentique.</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-2">4.3 Ce qui n'est pas couvert par la protection</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Les parfums, articles de beauté, lingerie, maillots de bain, chaussettes et collants ;</li>
                        <li>Les articles interdits : téléphones, tablettes et objets de valeur ;</li>
                        <li>L'article ne convient pas ou changement d'avis ;</li>
                        <li>Les opérations et transactions hors ligne.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Article 5 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 5 / Responsabilités</h2>
            <div class="space-y-3">
                <p>Usedis ne peut être tenu pour responsable ni du contenu des Annonces, ni des actions des Utilisateurs, ni des Articles mis en vente. Usedis n'intervient pas dans la transaction entre Acheteurs et Vendeurs et n'exerce aucun contrôle sur la qualité des Articles répertoriés.</p>
                <p>Usedis se réserve le droit de suspendre, sans préavis ni indemnité, l'accès au Site, temporairement ou définitivement, notamment pour des raisons de maintenance.</p>
                <p>Lorsqu'un litige survient entre les parties, Usedis pourra, pendant un délai de 30 jours, faire son possible pour aider les parties à trouver un accord.</p>
            </div>
        </div>

        {{-- Article 6 --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">Article 6 / Dispositions diverses</h2>
            <div class="space-y-3">
                <p>Les présentes Conditions Générales sont valables à compter du 26 AVRIL 2023.</p>
                <p>En s'inscrivant sur le Site, l'Utilisateur reconnaît avoir pris connaissance, compris et accepté sans réserve l'intégralité des présentes Conditions Générales. Usedis a la possibilité de modifier les présentes Conditions Générales en fonction de l'évolution de son offre et du marché.</p>
                <p>Le Site, son contenu, son catalogue, ses textes, ses illustrations, ses photographies et ses images sont la propriété de Usedis. Il est interdit de reproduire, représenter et/ou exploiter tout ou partie du Site sans autorisation préalable de Usedis.</p>
                <p>Tout refus d'acceptation des Conditions modifiées devra être notifié en envoyant un e-mail à <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a>.</p>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 text-center">
            <p>Une question concernant ces conditions ?</p>
            <p class="mt-1"><a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a> · <a href="https://www.usedis.com" class="text-gray-900 underline">www.usedis.com</a></p>
        </div>

    </div>
</div>
@endsection
