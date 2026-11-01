@extends('layouts.app')

@section('title', 'Politique de confidentialité')

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-3xl font-light text-center mb-4">Politique de confidentialité.</h1>
    <p class="text-center text-sm text-gray-500">La confiance est au cœur de notre marketplace. Cette politique explique comment USEDIS collecte, utilise et protège les données personnelles lorsque vous utilisez notre plateforme.</p>

    <dl class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-xl bg-gray-50 p-5 text-sm">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Éditeur</dt>
            <dd class="font-semibold text-gray-900">Usedigital</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Adresse du siège</dt>
            <dd class="font-semibold text-gray-900">AVN MANSOUR DEHBI, KENITRA</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Contact</dt>
            <dd class="font-semibold text-gray-900"><a href="mailto:contact@usedis.com" class="underline">contact@usedis.com</a></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Mise à jour</dt>
            <dd class="font-semibold text-gray-900">26 octobre 2026</dd>
        </div>
    </dl>

    <div class="mt-6 border border-gray-200 p-5 text-sm">
        <p class="font-bold uppercase text-gray-900">Votre vie privée compte.</p>
        <p class="mt-1 text-gray-600">Nous cherchons à limiter l'utilisation des données à ce qui est nécessaire au fonctionnement, à la sécurité et à l'amélioration du service.</p>
    </div>

    <div class="space-y-8 text-sm text-gray-600 leading-relaxed mt-10">

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">01. Introduction</h2>
            <div class="space-y-3">
                <p>Bienvenue sur USEDIS. USEDIS est une marketplace en ligne spécialisée dans la mode de seconde main, les vêtements premium, vintage et streetwear. La plateforme permet aux utilisateurs de découvrir des articles et, selon les fonctionnalités disponibles, de publier des annonces, d'acheter et de vendre des produits.</p>
                <p>La présente politique explique quelles données personnelles peuvent être collectées, pourquoi elles sont utilisées, avec qui elles peuvent être partagées et quels droits les utilisateurs peuvent exercer. USEDIS traite les données conformément à la législation applicable, notamment, le cas échéant, la loi marocaine n° 09-08 relative à la protection des personnes physiques à l'égard du traitement des données à caractère personnel.</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">02. Responsable du traitement</h2>
            <p>Le responsable du traitement des données personnelles est : Usedigital, dont le siège social est situé à AVN MANSOUR DEHBI, KENITRA. Pour toute question relative à la confidentialité ou aux données personnelles : <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a>.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">03. Données personnelles susceptibles d'être collectées</h2>
            <div class="space-y-3">
                <p><strong class="text-gray-800">Informations du compte :</strong> nom, prénom ou pseudonyme, adresse e-mail, numéro de téléphone, mot de passe sous forme sécurisée, photo de profil et préférences du compte.</p>
                <p><strong class="text-gray-800">Connexion Google :</strong> si l'utilisateur choisit « Continuer avec Google », USEDIS peut recevoir les informations autorisées lors de la connexion, telles que le nom, l'adresse e-mail et la photo de profil.</p>
                <p><strong class="text-gray-800">Annonces :</strong> photos, nom et description des articles, marque, catégorie, taille, état, prix et historique des annonces publiées.</p>
                <p><strong class="text-gray-800">Commandes et transactions :</strong> articles commandés, montants, historique et statut des commandes, informations nécessaires au suivi de la transaction et au service de protection des acheteurs.</p>
                <p><strong class="text-gray-800">Livraison :</strong> nom du destinataire, numéro de téléphone, adresse et informations nécessaires à l'acheminement.</p>
                <p><strong class="text-gray-800">Données techniques :</strong> adresse IP, type d'appareil, système d'exploitation, navigateur, données de connexion et interactions avec la plateforme, dans la mesure où ces données sont effectivement collectées.</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">04. Finalités d'utilisation</h2>
            <p>Les données peuvent être utilisées pour créer et gérer les comptes, permettre la publication d'annonces, faciliter les achats et les ventes, traiter et suivre les commandes, organiser la livraison, assurer l'assistance, sécuriser les comptes et transactions, prévenir les fraudes, améliorer la plateforme et respecter les obligations légales.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">05. Partage des données</h2>
            <p>USEDIS ne vend pas les données personnelles des utilisateurs. Certaines données peuvent être communiquées, dans la limite du nécessaire, aux catégories de destinataires suivantes : prestataires de livraison pour l'expédition et la réception des commandes ; prestataires de paiement lorsque leurs services sont utilisés ; Google lorsque l'utilisateur choisit l'authentification Google ; prestataires techniques intervenant dans l'hébergement, la maintenance ou la sécurité ; autorités compétentes lorsque la communication est exigée par une obligation légale ou une demande valable.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">06. Paiements et informations financières</h2>
            <p>Les commandes peuvent être soumises à des frais de service, de protection des acheteurs et de livraison, selon les conditions affichées au moment de la commande. Lorsque le paiement est traité par un prestataire externe, les informations financières nécessaires peuvent être traitées par ce prestataire conformément à ses propres règles.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">07. Cookies et technologies similaires</h2>
            <p>Le site peut utiliser des cookies ou technologies similaires pour assurer son fonctionnement, maintenir les sessions, mémoriser des préférences et, si des outils correspondants sont activés, mesurer l'audience ou améliorer le service. Les utilisateurs peuvent gérer les cookies depuis leur navigateur. Les cookies non essentiels doivent être soumis aux règles de consentement applicables.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">08. Durée de conservation</h2>
            <p>Les données sont conservées pendant la durée nécessaire aux finalités pour lesquelles elles ont été collectées. Certaines informations peuvent être conservées plus longtemps lorsque cela est nécessaire pour respecter des obligations légales ou comptables, traiter des réclamations, prévenir les fraudes ou défendre les droits de USEDIS.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">09. Sécurité</h2>
            <p>USEDIS met en œuvre des mesures techniques et organisationnelles adaptées afin de protéger les données contre l'accès non autorisé, la perte, la destruction, la modification ou la divulgation. Aucun système ne pouvant garantir une sécurité absolue, les utilisateurs sont invités à protéger leurs identifiants et à signaler toute activité suspecte.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">10. Transferts à l'étranger</h2>
            <p>Certains prestataires techniques peuvent traiter des données depuis des pays autres que le Maroc. Tout transfert international doit être encadré conformément aux exigences légales applicables et aux formalités éventuellement requises auprès de la CNDP.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">11. Droits des utilisateurs</h2>
            <div class="space-y-3">
                <p>Dans les conditions prévues par la législation applicable, les utilisateurs peuvent notamment demander des informations sur le traitement de leurs données, accéder aux données les concernant, demander leur rectification et exercer leur droit d'opposition ou de suppression lorsque les conditions légales sont réunies.</p>
                <p>Pour exercer ces droits, contactez : <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a>. Une vérification raisonnable de l'identité peut être demandée. Les utilisateurs peuvent également saisir la CNDP dans le cadre de ses compétences.</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">12. Services tiers</h2>
            <p>La plateforme peut intégrer des services tiers d'authentification, de paiement, de livraison, d'hébergement ou d'analyse. Ces services peuvent être soumis à leurs propres conditions et politiques de confidentialité.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">13. Modifications de la politique</h2>
            <p>USEDIS peut mettre à jour cette politique pour refléter l'évolution de ses services, de ses pratiques ou de la réglementation. La version en vigueur sera publiée sur cette page avec sa date de mise à jour. Les utilisateurs sont invités à la consulter régulièrement.</p>
        </div>

        <div>
            <h2 class="text-base font-bold text-gray-900 mb-3">14. Contact</h2>
            <p>Site web : <a href="https://usedis.com" class="text-gray-900 underline">https://usedis.com</a><br>E-mail : <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a></p>
        </div>

    </div>
</div>
@endsection
