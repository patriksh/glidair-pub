import { toastController } from '@ionic/vue';
import { Network } from '@capacitor/network';
import router from '@/router';

// Vrati korisnika na početnu stranicu i prikaži poruku ako korisnik nije pronađen.
export const userNotFound = () => {
    router.push({ name: 'home' });
    toastController.create({ message: 'Korisnik nije pronađen.', duration: 2000 }).then(t => t.present());

    return null;
}

// Vrati korisnika na početnu stranicu ako pokušava urediti korisnika dok nema internetske veze.
export const userNotFoundOffline = async () => {
    router.push({ name: 'home' });

    const message = (await Network.getStatus()).connected ? 'Korisnik nije pronađen.' : 'Korisnika nije moguće uređivati dok nema internetske veze.';
    toastController.create({ message, duration: 2000 }).then(t => t.present());

    return null;
}