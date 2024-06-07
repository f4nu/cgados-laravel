<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->boolean('public')->after('directory_id')->default(true);
        });

        (new \App\Models\File([
            'name' => 'flag.txt',
            'content' => <<<EOF
Ciao.
 
Innanzitutto grazie per aver giocato e avermi dato un obiettivo più o meno imminente in questi due ultimi mesi (o chissà quando starete leggendo questo messaggio).
 
Non sono bravissimo con queste cose e infatti ho ideato un ARG di mesi piuttosto che accodarmi ai ringraziamenti generici. Cambiamo dunque discorso: cos'è questa roba?
 
# La storia
Questo è un server scoperto quasi per caso, ormai abbandonato da millenni e ancora attivo in un luogo e tempo in cui probabilmente la civiltà non c'è più. È la mia visione di quello che potrebbe essere stata la storia della famosa Ferrero se Aperture avesse scelto di costruire il suo impero ad Alba, nelle sue famosissime miniere di sale. La mia basata opinione su una storia di un universo diverso dal nostro in cui Aperture ha avuto la possibilità di dare forma al Kinder Pinguì.
 
# CGaDOS
Durante gli anni succedono un sacco di cose, molte di queste strizzano l'occhio ad una collaborazione tra le due parti. La cosa interessante è lo scoprire che l'ultimo dipartito della dinastia Ferrero viene inserito in una AI per renderlo immortale.
 
# date e tracert
Come può essere passato così tanto tempo e dove si trova questo server? Dopo lunghe discussioni con un amico su tachioni, buchi neri e universi paralleli ho deciso di semplificare il tutto in un coattissimo collegamento "wireless" che punta esattamente al centro di uno dei buchi neri più vicini a noi, a soli ~3000 anni luce: ecco perché il server si trova nell'anno 5000 e rotti.
 
# INTERLOPE
Un colpo di culo e di genio, se non si fosse capito ho unito tutto un po' di pancia e per puro caso ha avuto senso e si è incastrato piuttosto bene nella storia.
 
# e quindi?
E quindi niente. Congratulazioni.
 
Un ringraziamento speciale in ordine assolutamente casuale alle persone per cui ho fatto tutto questo e che mi accompagnano più o meno tutti i giorni, senza le quali sarei già diventato pazzo. Pazzo? Sono stato pazzo in passato. Senza di loro mi avrebbero già chiuso in una stanza, una stanza di gomma, una stanza di gomma con dei ratti. E mi piacciono i ratti.
 
La Palma (non quella del Cocco)
Ollama
Visctas
Forteschi69
BaronKidd
Esposito d'Annunzio
Amos
Massimo Disk Jockey
SophisticatedOtus
Obergruppenhakai
Phia'dena
 
Lo staff di ISC
Tutte le persone che ho abbracciato a GUF
 
/seeYouSpaceCowboys
EOF,
            'directory_id' => \App\Models\Directory::query()->where('name', '=', 'fanu')->firstOrFail()->id,
            'public' => false,
        ]))->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('public');
        });
        
        \App\Models\File::where('name', 'flag.txt')->delete();
    }
};
