<?php

namespace Tests\Feature;

use App\Rules\FichierTeleverse;
use App\Support\TypeFichier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FichierTeleverseTest extends TestCase
{
    private function valider(UploadedFile $fichier): \Illuminate\Validation\Validator
    {
        return Validator::make(
            ['affiche' => $fichier],
            ['affiche' => ['nullable', new FichierTeleverse('affiche')]]
        );
    }

    public function test_les_formats_autorises_sont_acceptes(): void
    {
        foreach (['affiche.jpg', 'affiche.jpeg', 'affiche.png', 'affiche.webp'] as $nom) {
            $this->assertTrue($this->valider(UploadedFile::fake()->image($nom))->passes(), $nom);
        }
    }

    public function test_un_format_non_autorise_est_rejete_avec_un_message_explicite(): void
    {
        $validator = $this->valider(UploadedFile::fake()->create('affiche.pdf', 100, 'application/pdf'));

        $this->assertTrue($validator->fails());
        $this->assertSame(
            "Le format de l'affiche n'est pas autorisé. Formats acceptés : JPG, JPEG, PNG, WEBP.",
            $validator->errors()->first('affiche')
        );
    }

    public function test_un_svg_est_rejete(): void
    {
        $svg = UploadedFile::fake()->createWithContent('affiche.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->assertTrue($this->valider($svg)->fails());
    }

    public function test_une_extension_falsifiee_est_rejetee(): void
    {
        // Script PHP renommé en .jpg : le contenu réel trahit le faux format.
        $faux = UploadedFile::fake()->createWithContent('affiche.jpg', '<?php echo "pirate"; ?>');

        $this->assertTrue($this->valider($faux)->fails());
    }

    public function test_un_fichier_trop_volumineux_est_rejete_avec_un_message_explicite(): void
    {
        $type = TypeFichier::get('affiche');
        $fichier = UploadedFile::fake()->image('affiche.jpg')->size(intdiv($type->maxOctets, 1024) + 1);

        $validator = $this->valider($fichier);

        $this->assertTrue($validator->fails());
        $this->assertSame($type->messageTaille(), $validator->errors()->first('affiche'));
    }

    public function test_un_fichier_refuse_par_php_pour_sa_taille_donne_le_message_de_taille(): void
    {
        $chemin = tempnam(sys_get_temp_dir(), 'upl');
        $fichier = new UploadedFile($chemin, 'affiche.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);

        $validator = $this->valider($fichier);

        $this->assertTrue($validator->fails());
        $this->assertSame(TypeFichier::get('affiche')->messageTaille(), $validator->errors()->first('affiche'));
    }

    public function test_la_taille_annoncee_ne_depasse_jamais_la_limite_php(): void
    {
        $limitePhp = (int) ini_get('upload_max_filesize') * 1024 * 1024;

        $this->assertLessThanOrEqual($limitePhp, TypeFichier::get('affiche')->maxOctets);
        $this->assertLessThanOrEqual(config('uploads.types.affiche.max_ko') * 1024, TypeFichier::get('affiche')->maxOctets);
    }

    public function test_l_api_rejette_un_fichier_non_autorise(): void
    {
        $response = $this->postJson('/api/v1/demande-evenement', [
            'nom_evenement' => 'Concert',
            'contact_organisateur' => 'orga@example.com',
            'description' => 'Description',
            'type_evenement' => 'Concert',
            'statut' => 'en_attente',
            'affiche' => UploadedFile::fake()->create('affiche.exe', 10, 'application/octet-stream'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('affiche');
    }

    public function test_le_composant_affiche_les_regles_au_frontend(): void
    {
        $type = TypeFichier::get('affiche');

        $this->withViewErrors([])
            ->blade('<x-app-file-input name="photo_affiche" />')
            ->assertSee('accept="'.$type->accept().'"', false)
            ->assertSee('data-max-octets="'.$type->maxOctets.'"', false)
            ->assertSee('Formats acceptés : JPG, JPEG, PNG, WEBP');
    }
}
