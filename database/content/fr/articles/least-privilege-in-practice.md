Le moindre privilège signifie que chaque identité ne dispose que des accès dont elle a besoin, et seulement pendant le temps nécessaire. Trois questions permettent de le rendre concret.

## 1. Que doit-elle réellement faire ?

Formulez le besoin en mots simples avant d’écrire la politique : *« Lire les factures d’un seul bucket et écrire les journaux de l’application. »* Tout ce qui ne figure pas sur cette liste est en trop.

## 2. Où sont les caractères génériques ?

Un `*` dans une action ou une ressource est la source la plus fréquente de privilèges excessifs. Chaque caractère générique doit faire l’objet d’une justification écrite.

## 3. Cette permission peut-elle en accorder d’autres ?

Certaines permissions permettent à une identité de s’attribuer davantage d’accès : attacher des politiques, créer des clés pour d’autres identités ou transmettre des rôles à des services. Traitez-les comme des droits d’administration.

Révisez les accès régulièrement. Les permissions s’accumulent sans bruit, et le privilège le moins coûteux à retirer est celui que personne n’utilise.
