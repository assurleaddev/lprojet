@extends('layouts.app')

@section('title', 'FAQ')

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-4xl font-light text-center mb-4">FAQ.</h1>
    <p class="text-center text-sm text-gray-500 mb-12">Les réponses essentielles : acheter, vendre et profiter de la communauté Usedis en toute simplicité.</p>

    <div class="space-y-8">

        <div>
            <h2 class="text-base font-bold mb-2">1. Qu'est-ce que Usedis ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Usedis est un site de vide-dressing où chaque utilisateur peut vendre et acheter des vêtements neufs ou d'occasion.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">2. Comment obtenir l'application ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Tu peux télécharger l'application Usedis directement depuis le Play Store (Android) ou sur l'App Store (Apple).</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">3. Comment contacter le service client de Usedis ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Depuis la page Contactez-nous ou par téléphone au +212 657986862 ou par e-mail à <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a>.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">4. Comment démarrer ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-3">
                <div>
                    <p class="font-medium text-gray-700">Créer un compte sur Usedis</p>
                    <p>Inscris-toi via Facebook / Gmail en remplissant ton profil avec tes informations personnelles, ta taille et ta pointure.</p>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Créer ta liste des articles favoris</p>
                    <p>À chaque fois qu'un article te plaît, clique sur 'J'aime' — il sera ajouté à ta liste 'Coups de cœur'.</p>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Abonne-toi à la Usedis Community</p>
                    <p>qui a le même style et la même taille que toi.</p>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Achète dans les dressings des autres</p>
                    <p>Fais un tour et découvre ce qui pourrait faire ton bonheur.</p>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Crée ton dressing</p>
                    <p>Mets en ligne tes articles, prends-les en photo, ajoute une description et fixe ton prix.</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">5. Puis-je acheter et vendre si je suis en dehors du Maroc ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Actuellement, Usedis est disponible au Maroc. Notre site sera bientôt disponible dans d'autres pays, alors suivez-nous ;)</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">6. Comment s'inscrire ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Inscris-toi gratuitement en cliquant sur 'Connexion' ou 'Inscription'. Nous proposons la création de compte via Facebook — ça prendra 3 secondes ! PS : Nous ne publions rien sur ton mur Facebook ;)</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">7. Comment changer mon nom d'utilisateur ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Tu peux changer ton pseudo Usedis en allant sur la page 'Mon Profil'. Pense à remettre à jour ton profil régulièrement ;)</p>
        </div>

        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 pt-4">Votre compte — Profil, communauté &amp; assistance</h2>

        <div>
            <h2 class="text-base font-bold mb-2">8. Où trouver mon dressing ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Tu peux le trouver en cliquant sur 'Menu' puis 'Mon Dressing'.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">9. J'ai oublié mon mot de passe, que faire ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Il faut s'adresser à Facebook si tu as oublié ton mot de passe. Nous ne conservons aucun mot de passe chez nous.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">10. Quels types d'emails vais-je recevoir lorsque je m'inscris sur Usedis ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">En t'inscrivant sur Usedis, tu acceptes de recevoir des e-mails concernant tes achats, tes ventes et les notifications du site.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">11. Comment supprimer mon compte ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Pour supprimer ton compte, contacte-nous à <a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a> avec pour objet 'Supprimer mon compte Usedis'.</p>
        </div>

        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 pt-4">Acheter en confiance — Protection, vente &amp; livraison</h2>

        <div>
            <h2 class="text-base font-bold mb-2">12. Dois-je faire confiance aux utilisateurs ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">À chaque commande sur Usedis, tu profites de la 'Usedis Protection'. Ton argent est conservé par Usedis jusqu'à ta confirmation de la bonne réception et de la conformité de l'article. Tu as 24h pour faire une réclamation. Après ce délai sans réclamation, Usedis vire l'argent au vendeur.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">13. Qu'est-ce que la 'Usedis Protection' ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-4">
                <p>Usedis t'offre cette protection à chaque commande, au cas où tu constates un défaut ou une non-conformité de l'article acheté avec la description sur le site.</p>
                <div>
                    <p class="font-medium text-gray-700">Le remboursement sur Usedis</p>
                    <p class="mt-1">Ton argent n'est pas versé au vendeur avant 24h après la réception. Tu as 24h pour signaler tout problème par mail avec des photos. Une fois la réclamation validée, tu as 3 jours pour expédier l'article. Usedis te remboursera dès que le retour sera enregistré.</p>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Ce qui est couvert</p>
                    <ul class="mt-1 list-disc pl-5 space-y-1">
                        <li>Dommages non divulgués ;</li>
                        <li>Article incorrect ou manquant ;</li>
                        <li>L'article ne correspond pas à la description ;</li>
                        <li>L'article n'est pas authentique.</li>
                    </ul>
                </div>
                <div>
                    <p class="font-medium text-gray-700">Ce qui n'est pas couvert</p>
                    <ul class="mt-1 list-disc pl-5 space-y-1">
                        <li>Changement d'avis : Si l'article ne te convient pas, nous ne pouvons pas accepter un retour. Tu peux le remettre en vente sur www.usedis.com.</li>
                        <li>Transactions hors ligne : Usedis Protection ne couvre que les opérations effectuées sur la plateforme.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">14. Que puis-je vendre ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Vous pouvez vendre sur Usedis : vêtements, accessoires, chaussures. Donne une seconde vie à tes affaires !</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">15. Comment vendre (en détails) ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-4">
                <div>
                    <p class="font-medium text-gray-700 mb-1">Mise en ligne de l'article</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Prends une jolie photo de ton article ;</li>
                        <li>Clique sur 'Je vends un article' ;</li>
                        <li>Ajoute un titre, catégorie, marque, taille, état et couleur ;</li>
                        <li>Fixe un prix de vente et renseigne ta ville ;</li>
                        <li>Télécharge de 1 à 4 photos et remplis la description ;</li>
                        <li>Clique sur 'Je vends' — et c'est parti !</li>
                    </ul>
                </div>
                <div>
                    <p class="font-medium text-gray-700 mb-1">Envoi du colis</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Prépare un colis soigneusement ;</li>
                        <li>Un livreur passera le récupérer et te donnera une décharge et un numéro de suivi ;</li>
                        <li>Article livré — reçois ton paiement et laisse un avis !</li>
                    </ul>
                </div>
                <p class="text-red-600 text-xs font-medium">Important : il est interdit d'expédier des articles sales, abîmés ou contrefaits !</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">16. Quelle est la commission de Usedis ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">La mise en ligne est gratuite. La commission est de 20% HT du prix de l'article, prélevée uniquement après la vente finalisée.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">17. Quels sont les frais de livraison sur Usedis ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Les frais de livraison sont pris en charge par l'acheteur. Le montant est spécifié sur la page de l'article.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">18. Comment j'envoie mon colis ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-1">
                <p>Prépare le colis soigneusement dans un paquet adapté ;</p>
                <p>Un livreur passera le récupérer et te donnera une décharge et un numéro de suivi.</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">19. Quel type de boîte dois-je utiliser ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Une boîte de chaussures neuve ou tout autre type de boîte fera l'affaire. L'essentiel est que le colis soit bien préparé ;)</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">20. Comment recevoir mon argent ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Au bout de 24h sans réclamation de l'acheteur, la vente est validée et tu recevras ton argent par virement bancaire.</p>
        </div>

        <div class="border-t border-gray-200 pt-6 text-center text-sm text-gray-600">
            <p>Une question ?</p>
            <p class="mt-1"><a href="mailto:contact@usedis.com" class="text-gray-900 underline">contact@usedis.com</a> · +212 657986862 · <a href="https://www.usedis.com" class="text-gray-900 underline">www.usedis.com</a></p>
        </div>

    </div>
</div>
@endsection
