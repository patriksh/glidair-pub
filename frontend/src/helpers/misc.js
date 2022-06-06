import countries from '@/data/countries.json';
import { isPlatform } from '@ionic/vue';

// Vrati popis svih država.
export const getCountries = () => {
    let countryArray = [];

    for(const [code, name] of Object.entries(countries)) {
        countryArray.push({ code, name });
    }

    return countryArray;
}

// Pronađi ime države po kodu.
export const getCountryName = (value) => {
    return value in countries ? countries[value] : '';
}

// Sortiranje liste po imenu ili datumu.
export const nameAndDateSorter = (params) => {
    return (a, b) => {
        if(params.orderColumn == 'name') {
            if(params.orderType == 'asc') {
                return b.name.localeCompare(a.name);
            } else {
                return a.name.localeCompare(b.name);
            }
        } else if(params.orderColumn == 'created_at') {
            if(params.orderType == 'asc') {
                return new Date(a.created_at) - new Date(b.created_at);
            } else {
                return new Date(b.created_at) - new Date(a.created_at);
            }
        }
    
        return 0;
    }
}

// Provjeri je li app otvoren sa desktopa/laptopa/tableta.
export const isDesktop = () => {
    return (isPlatform('desktop') || isPlatform('tablet')) && (!isPlatform('mobile') || isPlatform('ipad'));
}

// Podijeli listu u više dijelova neke veličine.
const splitIntoChunks = (arr, size) => {
    let split = [];

    for(let i = 0; i < arr.length; i += size) {
        split.push(arr.slice(i, i + size));
    }

    return split;
}

// Spoji neki broj dijelova liste u jednu listu.
const combineChunks = (arr, amount) => {
    let combine = [];

    arr.slice(0, amount + 1).map(a => combine = combine.concat(a));

    return combine;
}

export const prepareForInfiniteScroll = (arr, page, perPage) => {
    return combineChunks(splitIntoChunks(arr, perPage), page);
}