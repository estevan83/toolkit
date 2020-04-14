https://help.github.com/en/github/writing-on-github/basic-writing-and-formatting-syntax

# Helper toolkit

## Installazione
Copiare la folder toolkit nella root 

## Files e spiegazione


expcvid.php cvid [newcvid]

Prendi in ingresso ID di una custom view e genera le query estraendo i valori e quidni la rende indipendente dal db di partenza.
Se indicato [newcvid] lo esporta con questo nuovo id

```
expcvmod.php host user password database module
```
Prende in ingresso un module es Accounts di una custom view e genera le query collegate al db di partenza.
I primi tre parametri sono i parametri di connessione al database (per quando ci viene fornito solo il dump del database e non tutta l'installazione