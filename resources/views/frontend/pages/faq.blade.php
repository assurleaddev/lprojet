@extends('layouts.app')

@section('title', 'FAQ')

@section('content')
<div class="shell px-4 md:px-6 py-12 max-w-3xl mx-auto">
    <h1 class="text-4xl font-light text-center mb-12">FAQ.</h1>

    <div class="space-y-8">

        <div>
            <h2 class="text-base font-bold mb-2">1. Qu'est-ce que USED ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">USED est un site de vide-dressing où chaque utilisateur peut vendre et acheter des vêtements neufs ou d'occasion.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">2. Comment obtenir l'application ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Tu peux télécharger l'application USED directement depuis le Play Store (Android) ou sur l'App Store (Apple).</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">3. Comment contacter le service client de USED ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Depuis la page Contactez-nous ou par téléphone au +212 657986862 ou <a href="mailto:contact@used.ma" class="text-gray-900 underline">contact@used.ma</a>.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">4. Comment démarrer ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-3">
                <p>- Crée un compte sur USED : Inscris-toi sur via Facebook / Gmail en remplissant ton profil avec des informations personnelles et tes préférences avec ta taille et ta pointure pour nous aider à filtrer et trouver les meilleurs articles qui te correspondent.</p>
                <p>- Crée ta liste des articles favoris : à chaque fois qu'un article te plait, tu peux cliquer sur j'aime et automatiquement, il sera ajouté à la liste 'coups de cœur'.</p>
                <p>- Abonne-toi aux USED Community qui ont le même style et la même taille que toi : si tu as le même goût et la taille qu'un USED,</p>
                <p>- Achète dans les dressings des autres USED : fais un tour sur USED, et regarde dans les dressings des autres ce qui pourrait faire ton bonheur.</p>
                <p>- Crée ton dressing : mets en ligne les articles pour les vendre. Prends-les en photo, ajoute une description, fixe ton prix et le tour est joué !</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">5. Puis-je acheter et vendre si je suis en dehors du Maroc ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Actuellement, USED est disponible au Maroc. Notre site sera bientôt disponible dans d'autres pays, alors suivez nous ;)</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">6. Comment s'inscrire ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-2">
                <p>Inscris-toi gratuitement en cliquant sur le bouton 'Connexion' ou 'Inscription'.</p>
                <p>Nous te proposons uniquement la création de compte via Facebook, ça prendra 3 secondes !<br>PS : Ne t'inquiète surtout pas, nous ne publions rien sur ton mur Facebook ;)</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">7. Comment changer mon nom d'utilisateur ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Tu peux changer ton pseudo USED en allant la page 'Mon Profil'.<br>Pense à remettre à jour ton profil sur USED régulièrement ;)</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">8. Où trouver mon dressing ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Chaque USED a son propre dressing sur USED.<br>Tu peux trouver en cliquant sur 'Menu' puis 'Mon Dressing'.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">9. J'ai oublié mon mot de passe, que faire ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Il faut s'adresser à Facebook si tu as oublié ton mot de passe. Nous ne conservons aucun mot de passe chez nous.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">10. Quels types d'emails vais-je recevoir lorsque je m'inscris sur USED ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">En t'inscrivant sur USED, tu acceptes de recevoir des e-mails concernant tes achats, tes ventes et les notifications du site.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">11. Comment supprimer mon compte ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">C'est bien dommage de te voir nous quitter :(<br>Pour supprimer ton compte, tu peux nous contacter par mail : <a href="mailto:contact@used.ma" class="text-gray-900 underline">contact@used.ma</a>. Avec pour objet 'supprimer mon compte USED'.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">12. Dois-je faire confiance aux utilisateurs ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">A chaque fois que tu passes une commande sur USED, tu profites de la 'USED Protection'. Quand tu effectues ton paiement, en ligne ou à la livraison, ton argent sera récupéré par USED en attendant ta confirmation de la bonne réception de ton article et surtout de sa conformité avec la description sur le site. Tu as 24h pour nous faire une réclamation concernant un défaut ou la non-conformité de ton article ou nous confirmer que tout est bon. Après 24h de la réception, sans réclamation de ta part, on considère que tu es satisfaite. USED vire l'argent à Le vendeur.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">13. Qu'est-ce que USED Protection' ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-4">
                <p>USED t'offre cette protection à chaque fois que tu passes une commande sur le site, au cas où tu constates un défaut ou une non-conformité de l'article acheté avec la description sur le site.</p>

                <div>
                    <p class="font-medium text-gray-700">* Le remboursement sur USED</p>
                    <p class="mt-1">Quand tu effectues un achat sur USED, ton argent n'est pas versé à la vendeuse qu'après 24h de la réception de ton article. Tu as, alors 24h après la livraison, pour nous signaler tout problème avec ta commande. Tu peux effectuer la réclamation par mail et en nous envoyant des photos expliquant la nature du problème. L'équipe de USED vérifie ta réclamation, et t'envoies un bordereau prépayé pour retourner l'article à la vendeuse. Une fois la réclamation validée, tu as 3 jours pour expédier l'article. USED te remboursera dès que le retour sera enregistré à la poste.</p>
                </div>

                <div>
                    <p class="font-medium text-gray-700">* Ce qui est couvert par la protection</p>
                    <p class="mt-1">Si l'un des éléments suivants se rapporte à ta commande, merci de nous signaler le problème immédiatement dans l'application ou le site Web de USED :</p>
                    <ul class="mt-2 space-y-1 list-none">
                        <li>- Dommages non divulgués</li>
                        <li>- Article incorrect ou manquant</li>
                        <li>- L'article ne correspond pas à la description</li>
                        <li>- L'article n'est pas authentique</li>
                    </ul>
                    <p class="mt-2">Si aucune réclamation n'est faite dans les 24h après de la livraison, le paiement sera automatiquement effectué auprès de la vendeuse. Une fois le paiement effectué, toutes les ventes sont finalisées et aucun remboursement ne sera effectué.</p>
                </div>

                <div>
                    <p class="font-medium text-gray-700">* Ce qui n'est pas couvert par la protection</p>
                    <p class="mt-1">Ce qui n'est pas couvert :</p>
                    <div class="mt-2 space-y-3">
                        <div>
                            <p>- L'article ne convient pas ou changement d'avis</p>
                            <p class="text-gray-500 text-xs mt-1">Si l'article n'est tout simplement pas à ton gout finalement ou ne te convient pas, nous ne pouvons malheureusement pas accepter un retour. Tu peux à ton tour le remettre en vente sur Used.ma.</p>
                        </div>
                        <div>
                            <p>- Les opérations et les transactions hors ligne</p>
                            <p class="text-gray-500 text-xs mt-1">USED protection ne couvre que les opérations effectuées sur la plate-forme. Lorsque le paiement de la valeur totale des articles n'est pas échangé via la plate-forme, nous ne pouvons pas garantir que les articles soient envoyés ou conformes. Tu assumeras tous les risques associés à ces transactions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">14. Que puis-je vendre ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Vous pouvez vendre sur USED : vêtements, accessoires, chaussures.<br><br>Fais le tri dans ton dressing. Tu vois ces affaires que tu n'utilises plus ? Prends-les en photos pour leur donner une seconde vie.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">15. Comment vendre (En détails) ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-4">
                <p>Tu es décidée à te séparer de cette chemise que tu ne mets plus depuis des années ?<br>Voici comment vendre en 3 étapes :</p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Mets en ligne ton article en vente.</li>
                    <li>Sois disponible pour renseigner et accepter les commandes.</li>
                    <li>C'est vendu ! Tu peux confier ton colis à nos livreurs.</li>
                </ol>

                <div>
                    <p class="font-medium text-gray-700 mb-2">Mise en ligne des articles en vente</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Prends une jolie photo de ton article</li>
                        <li>Clique sur 'Je vends un article' (en haut à gauche), si tu es sur ordinateur. Si tu es sur l'application mobile, clique sur l'icône.</li>
                        <li>Ajoute un titre qui résume ce que tu vends (Ex : chemise noir et blanc Zara T38)</li>
                        <li>Choisis la catégorie de l'article</li>
                        <li>Choisis la sous-catégorie de l'article</li>
                        <li>Fixe un prix de vente (PS : le prix d'achat n'est pas obligatoire mais c'est plus vendeur de faire apparaitre le pourcentage de rabais donc si tu le connais on te conseille de le mettre)</li>
                        <li>Choisis la marque de ton article (si ton article est sans marque, choisis 'sans marque')</li>
                        <li>Choisis la taille, l'état et la couleur de ton article</li>
                        <li>Renseigne ta ville</li>
                        <li>Télécharge d'une à quatre belles photos</li>
                        <li>Remplis précisément la description de ton article</li>
                        <li>Clique sur 'Je vends'</li>
                    </ol>
                    <p class="mt-3">Et le tour est joué ! Ton article commence son aventure sur USED et part vers une seconde vie chez une autre utilisateur ;)</p>
                    <p class="mt-2">Ton article est en ligne : sois disponible pour renseigner les utilisateurs par commentaire.</p>
                    <p class="mt-2">- les notifications : tu es notifiée sur le site à chaque fois qu'un utilisateur aime ou commente ton article. Sois disponible pour répondre à ses questions, poliment et dans les plus brefs délais.</p>
                </div>

                <div>
                    <p class="font-medium text-gray-700 mb-2">Envoi du colis</p>
                    <p class="text-green-600 font-medium mb-2">Félicitations ! Ton article a été vendu, tu peux préparer ton colis</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Prépare un colis soigneusement</li>
                        <li>Un livreur passera le récupérer et te donnera une décharge et un numéro de suivi</li>
                        <li>Article livré ! Reçois ton paiement. Et n'oublies pas de laisser un avis sur l'acheteuse</li>
                    </ol>
                    <p class="mt-2 text-red-600 text-xs font-medium">Il est interdit d'expédier des articles sales, abîmés ou contrefaits !</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">16. Quelle est la commission de USED ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">La mise en ligne d'un article est gratuite.<br>La commission USED n'est prélevée qu'une fois la vente finalisée. Elle est de 20 % HT (Hors Taxes) du prix de l'article. Pour plus de détails, veuillez consulter les conditions générales d'utilisation.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">17. Quels sont les frais de livraison sur USED ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Les frais de livraison sont pris en charge par l'acheteuse. Le montant est spécifié sur la page de l'article.</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">18. Comment j'envoie mon colis ?</h2>
            <div class="text-gray-600 text-sm leading-relaxed space-y-1">
                <p>1. Prépare le colis soigneusement dans un paquet adapté</p>
                <p>2. Un livreur passera chez toi le récupérer et le remettra à l'acheteuse. Il te donnera une décharge et un numéro de suivi.</p>
            </div>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">19. Quel type de boite dois-je utiliser pour expédier mon colis ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Une boite de chaussures neuve ou tout autre type de boite fera l'affaire. Il faut que ce soit préparé soigneusement ;)</p>
        </div>

        <div>
            <h2 class="text-base font-bold mb-2">20. Comment recevoir mon argent ?</h2>
            <p class="text-gray-600 text-sm leading-relaxed">Ton colis est bien arrivé chez l'acheteuse. Au bout de 24h sans réclamation de sa part, la vente est validée et tu recevras ton argent par virement.</p>
        </div>

    </div>
</div>
@endsection
