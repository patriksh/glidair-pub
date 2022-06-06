import { toastController } from '@ionic/vue';
import { format, parseISO } from 'date-fns';
import hr from 'date-fns/locale/hr';
import { Network } from '@capacitor/network';
import router from '@/router';

// Vrati korisnika na početnu stranicu i prikaži poruku ako natjecanje nije pronađeno.
export const competitionNotFound = () => {
    router.push({ name: 'home' });
    toastController.create({ message: 'Natjecanje nije pronađeno.', duration: 2000 }).then(t => t.present());
    
    return null;
}

// Vrati korisnika na početnu stranicu ako pokušava urediti natjecanje dok nema internetske veze.
export const competitionNotFoundOffline = async () => {
    router.push({ name: 'home' });

    const message = (await Network.getStatus()).connected ? 'Natjecanje nije pronađeno.' : 'Natjecanje nije moguće uređivati dok nema internetske veze.';
    toastController.create({ message, duration: 2000 }).then(t => t.present());

    return null;
}

// Vrati puni URL logotipa natjecanja.
export const competitionLogoUrl = (logo) => `${process.env.VUE_APP_URL}/uploads/${logo}`;

// Promijeni format datuma za prikaz.
export const competitionDateFormatted = (date) => format(parseISO(date), 'dd MMM yyyy', { locale: hr });

// Filtriraj ljestvicu poretka natjecanja (država, spol i klub).
export const competitionResultFilter = (results, filters) => {
    if(!results) return null;
    if(!filters) return results;

    return results.filter(participant => {
        let test = true;

        if(filters.country != '') {
            test = test && participant.user.country == filters.country;
        }

        if(filters.gender != '') {
            test = test && participant.user.gender == filters.gender;
        }

        if(filters.club != '') {
            test = test && participant.user.club_id == filters.club;
        }

        return test;
    }).sort((a, b) => a.score - b.score);
}