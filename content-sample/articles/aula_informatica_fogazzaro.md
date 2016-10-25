/*
Title: Un'Aula informatica open source per la scuola elementare A. Fogazzaro (Chiesanuova)
Description: Descrizione dell'intervento effettuato presso la scuola elementare A.Fogazzaro di Chiesanuova
Date: 24/10/2016
Author: Paolo Scaramuzza
*/

# Un'aula informatica open source

A Giugno di quest'anno il nostro socio Federico ci segnala la necessità di
ampliare e rinnovare l'aula informatica della scuola elementare Antonio
Fogazzaro di Chiesanuova (PD).

Il primo passo è stato quello di recarci sul posto per capire la situazione e le
richieste dei docenti. Ci è stato detto di portare il numero di PC a 22,
fornendo la possibilità all'insegnante di condividere i file con gli alunni.
Tutto questo garantendo la massima facilità di utilizzo e manutenzione e
rendendo sicura la navigazione in internet dei bambini.

Erano già presenti una decina di computer, la maggior parte dei quali piuttosto
datati. Su alcuni era già stato installato Linux Mint ma nessuno era stato in
grado di configurare correttamente la rete.

## La soluzione:
Ci siamo subito attivati per fornire alla scuola 17 computer (16 per le
postazioni e uno da usare come server) e alcuni monitor. Si è discusso a lungo
su quale distribuzione utilizzare. Subito si è pensato ad Edubuntu, anche se
purtroppo [è stato abbandonato](http://www.gnulinuxfeed.space/edubuntu-16-04-non-si-fara-il-capolinea-e-vicino-549.html)
e l'interfaccia grafica è pesante per i computer più datati.

Tra i *repository* di Ubuntu sopravvive tuttavia il meta-pacchetto
`edubuntu-desktop` che installa tutto il software normalmente presente sulla
distribuzione di Edubuntu. La scelta è quindi ricaduta su
[Lubuntu](http://lubuntu.net/), variante leggera ma comunque graficamente
accattivante di Ubuntu, su cui sono stati installati tutti i pacchetti
provenienti dal mondo di Edubuntu. Anche gli altri computer della scuola sono
hanno ricevuto questo sistema operativo, in modo da avere ovunque la stessa
interfaccia.

Discutendone tra soci abbiamo deciso di organizzare il sistema come mostrato in
figura, ovvero con un server centrale che gestisce l'autenticazione degli utenti
e fornisce le loro *home directory* da due dischi in RAID 1 (realizzato con
[btrfs](https://btrfs.wiki.kernel.org/index.php/Using_Btrfs_with_Multiple_Devices)).
<img style="margin-bottom: 1.5em" src="/content-sample/articles/fogazzaro/network.png" alt="Organizzazione della rete">

Gli indirizzi IP delle varie macchine sono quelli che, come ci è stato riferito,
erano precedentemente assegnati ai computer dell'aula informatica. Come tale
abbiamo scelto una numerazione progressiva che partendo dal server procede verso
le varie postazioni.

Non esistendo nessun server DHCP gli indirizzi sono stati impostati manualmente
tramite `systemd-networkd` (una guida per smanettoni [qui](https://wiki.archlinux.org/index.php/Systemd-networkd)).
Sotto vedete la configurazione del server. Gli altri PC seguono a ruota.

<img class="img-small" src="/content-sample/articles/fogazzaro/interface.jpg" alt="Configurazione dell'interfaccia di rete">

Notate in particolare gli indirizzi DNS utilizzati. Sono quelli del servizio
[OpenDNS FamilyShield](https://blog.opendns.com/2010/06/23/introducing-familyshield-parental-controls/).
Questi DNS bloccano tutto i contenuti non adatti ai bambini che possono trovarsi
sul web, rendendo più sicura la navigazione.

## Configurazione di LDAP
Gli account utente vengono distribuiti dal server tramite il protocollo
[LDAP](https://it.wikipedia.org/wiki/Lightweight_Directory_Access_Protocol).
Dagli insegnanti ci è stato chiesto di avere un account utente per classe,
perciò abbiamo previsto di creare i vari utenti aggiungendone uno ridondante.

La nomenclatura degli account segue uno schema ben preciso del tipo
`lab-nomeclasse`, ad esempio: `lab-1a` per la IA, `lab-1b` per la IB, eccetera.

I vari bambini useranno la *home directory* della propria classe come spazio di
lavoro condiviso tra loro e con l'insegnante. Creeranno poi una cartella con il
loro nome dove salvare gli esercizi proposti a lezione. Questi file saranno
leggibili in tempo reale sul server dall'insegnante che potrà tenere traccia del
progresso dei propri alunni.

Ora un po'di dettagli da nerd su come è stato configurato il servizio LDAP sul 
*server* e sui *client* e come sono gestiti i permessi su file e *directory*.
Se la cosa non ti interessa salta pure alla prossima sezione.

**Continua nella prossima puntata...**

## Il risultato:
Dopo tanta fatica siamo riusciti a consegnare il materiale alla scuola e
configurare a dovere tutte le macchine. Attualmente manca ancora la parte di
LDAP ma per il resto ci siamo. 

Gli alunni possono connettersi ad internet ed usare i loro programmi preferiti.
In questa foto ne vedete un gruppetto alle prese con i nuovi PC. È entusiasmante
vedere con che facilità si destreggiavano con queste macchine lanciando alcuni
giochi e scrivendo una lista della spesa.
<img src="/content-sample/articles/fogazzaro/bimbi.jpg" alt="Bambini felici per la loro nuova aula informatica">

Che altro dire? La soddisfazione di aver portato il *software open source* anche
in questa realtà è grande. La flessibilità di configurazione di Linux ha
permesso di creare con ~~non~~ poca fatica la soluzione flessibile e di facile
mantenimento che ci era stata richiesta.

Alla luce di quest'esperienza gli insegnanti ci hanno chiesto di spostare alcuni
dei computer dall'aula informatica alle varie classi (uno per classe) ed anche
alcune brevi lezioni su come usare il loro nuovo sistema, il che testimonia
l'utilità dell'intervento che abbiamo svolto.

Alla prossima quindi!
