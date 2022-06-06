<template>
    <ion-list lines="none">
        <ion-item @click="onClick" button>{{ text }}</ion-item>
    </ion-list>
</template>

<script>
import { IonList, IonItem, alertController, popoverController } from '@ionic/vue';

export default {
    name: 'DeletePopover',
    components: { IonList, IonItem },
    props: ['delete', 'text', 'message'],
    setup(props) {
        // Izbriši korisnik/klub (pitaj za potvrdu).
        const onClick = async () => {
            const alert = await alertController.create({
                header: 'Brisanje',
                message: props.message,
                buttons: [
                    {
                        text: 'Odustani',
                        role: 'cancel',
                        cssClass: 'secondary'
                    },
                    {
                        text: 'Potvrdi',
                        role: 'confirm',
                        handler: () => {
                            props.delete();
                        },
                    }
                ]
            });
            
            // Otvori alert, zatvori popover nakon zatvaranja modala.
            await alert.present();
            await alert.onDidDismiss();
            popoverController.dismiss();
        };

        return {
            onClick
        }
    }
};
</script>