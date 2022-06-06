import { Network } from '@capacitor/network';
import { toastController } from '@ionic/vue';

// Provjeri ima li internetske veze.
export const isOnline = async () => (await Network.getStatus()).connected;

// Pokaži poruku da nema internetske veze.
export const offlineToast = async () => {
    toastController.create({ message: 'Nema internetske veze. Podaci su možda zastarjeli.', duration: 2000 }).then(t => t.present());
}