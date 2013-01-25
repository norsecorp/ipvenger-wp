/**
cut and pasted from current version of jVector map - must be verified
whenever jVectorMap is updated 

Whenever upgraded, country names must be changed both here and in world-en.js
for consistence with the IPV standard country names, which must also be kept
current in the all_countries array below

**/
 var countries = { 
	"BD": "Bangladesh" , "BE": "Belgium" , "BF": "Burkina Faso" , "BG": "Bulgaria" , "BA": "Bosnia and Herzegovina" , "BN": "Brunei Darussalam" , "BO": "Bolivia" , "JP": "Japan" , "BI": "Burundi" , "BJ": "Benin" , "BT": "Bhutan" , "JM": "Jamaica" , "BW": "Botswana" , "BR": "Brazil" , "BS": "Bahamas" , "BY": "Belarus" , "BZ": "Belize" , "RU": "Russian Federation" , "RW": "Rwanda" , "RS": "Serbia" , "LT": "Lithuania" , "LU": "Luxembourg" , "LR": "Liberia" , "RO": "Romania" , "GW": "Guinea Bissau" , "GT": "Guatemala" , "GR": "Greece" , "GQ": "Equatorial Guinea" , "GY": "Guyana" , "GE": "Georgia" , "GB": "United Kingdom" , "GA": "Gabon" , "GN": "Guinea" , "GM": "Gambia" , "GL": "Greenland" , "KW": "Kuwait" , "GH": "Ghana" , "OM": "Oman" , "_3": "Somaliland" , "_2": "Western Sahara" , "_1": "Kosovo" , "_0": "Northern Cyprus" , "JO": "Jordan" , "HR": "Croatia" , "HT": "Haiti" , "HU": "Hungary" , "HN": "Honduras" , "PR": "Puerto Rico" , "PS": "Palestinian Territory" , "PT": "Portugal" , "PY": "Paraguay" , "PA": "Panama" , "PG": "Papua New Guinea" , "PE": "Peru" , "PK": "Pakistan" , "PH": "Philippines" , "PL": "Poland" , "ZM": "Zambia" , "EE": "Estonia" , "EG": "Egypt" , "ZA": "South Africa" , "EC": "Ecuador" , "AL": "Albania" , "AO": "Angola" , "KZ": "Kazakhstan" , "ET": "Ethiopia" , "ZW": "Zimbabwe" , "ES": "Spain" , "ER": "Eritrea" , "ME": "Montenegro" , "MD": "Moldova, Republic of" , "MG": "Madagascar" , "MA": "Morocco" , "UZ": "Uzbekistan" , "MM": "Myanmar" , "ML": "Mali" , "MN": "Mongolia" , "MK": "Macedonia" , "MW": "Malawi" , "MR": "Mauritania" , "UG": "Uganda" , "MY": "Malaysia" , "MX": "Mexico" , "VU": "Vanuatu" , "FR": "France" , "FI": "Finland" , "FJ": "Fiji" , "FK": "Falkland Islands (Malvinas)" , "NI": "Nicaragua" , "NL": "Netherlands" , "NO": "Norway" , "NA": "Namibia" , "NC": "New Caledonia" , "NE": "Niger" , "NG": "Nigeria" , "NZ": "New Zealand" , "NP": "Nepal" , "CI": "Cote d'Ivoire" , "CH": "Switzerland" , "CO": "Colombia" , "CN": "China" , "CM": "Cameroon" , "CL": "Chile" , "CA": "Canada" , "CG": "Congo" , "CF": "Central African Republic" , "CD": "Congo, The Democratic Republic of the" , "CZ": "Czech Republic" , "CY": "Cyprus" , "CR": "Costa Rica" , "CU": "Cuba" , "SZ": "Swaziland" , "SY": "Syrian Arab Republic" , "KG": "Kyrgyzstan" , "KE": "Kenya" , "SS": "South Sudan" , "SR": "Suriname" , "KH": "Cambodia" , "SV": "El Salvador" , "SK": "Slovakia" , "KR": "Korea, Republic of" , "SI": "Slovenia" , "KP": "Korea, Democratic People's Republic of" , "SO": "Somalia" , "SN": "Senegal" , "SL": "Sierra Leone" , "SB": "Solomon Islands" , "SA": "Saudi Arabia" , "SE": "Sweden" , "SD": "Sudan" , "DO": "Dominican Republic" , "DJ": "Djibouti" , "DK": "Denmark" , "DE": "Germany" , "YE": "Yemen" , "AT": "Austria" , "DZ": "Algeria" , "US": "United States" , "LV": "Latvia" , "UY": "Uruguay" , "LB": "Lebanon" , "LA": "Lao People's Democratic Republic" , "TW": "Taiwan" , "TT": "Trinidad and Tobago" , "TR": "Turkey" , "LK": "Sri Lanka" , "TN": "Tunisia" , "TL": "Timor-Leste" , "TM": "Turkmenistan" , "TJ": "Tajikistan" , "LS": "Lesotho" , "TH": "Thailand" , "TF": "French Southern and Antarctic Lands" , "TG": "Togo" , "TD": "Chad" , "LY": "Libyan Arab Jamahiriya" , "AE": "United Arab Emirates" , "VE": "Venezuela" , "AF": "Afghanistan" , "IQ": "Iraq" , "IS": "Iceland" , "IR": "Iran, Islamic Republic of" , "AM": "Armenia" , "IT": "Italy" , "VN": "Vietnam" , "AR": "Argentina" , "AU": "Australia" , "IL": "Israel" , "IN": "India" , "TZ": "Tanzania, United Republic of" , "AZ": "Azerbaijan" , "IE": "Ireland" , "ID": "Indonesia" , "UA": "Ukraine" , "QA": "Qatar" , "MZ": "Mozambique"
	}; 

	var codes = { 
	"Bangladesh": "BD", "Belgium": "BE", "Burkina Faso": "BF", "Bulgaria": "BG", "Bosnia and Herzegovina": "BA", "Brunei Darussalam": "BN", "Bolivia": "BO", "Japan": "JP", "Burundi": "BI", "Benin": "BJ", "Bhutan": "BT", "Jamaica": "JM", "Botswana": "BW", "Brazil": "BR", "Bahamas": "BS", "Belarus": "BY", "Belize": "BZ", "Russian Federation": "RU", "Rwanda": "RW", "Serbia": "RS", "Lithuania": "LT", "Luxembourg": "LU", "Liberia": "LR", "Romania": "RO", "Guinea Bissau": "GW", "Guatemala": "GT", "Greece": "GR", "Equatorial Guinea": "GQ", "Guyana": "GY", "Georgia": "GE", "United Kingdom": "GB", "Gabon": "GA", "Guinea": "GN", "Gambia": "GM", "Greenland": "GL", "Kuwait": "KW", "Ghana": "GH", "Oman": "OM", "Somaliland": "_3", "Western Sahara": "_2", "Kosovo": "_1", "Northern Cyprus": "_0", "Jordan": "JO", "Croatia": "HR", "Haiti": "HT", "Hungary": "HU", "Honduras": "HN", "Puerto Rico": "PR", "Palestinian Territory": "PS", "Portugal": "PT", "Paraguay": "PY", "Panama": "PA", "Papua New Guinea": "PG", "Peru": "PE", "Pakistan": "PK", "Philippines": "PH", "Poland": "PL", "Zambia": "ZM", "Estonia": "EE", "Egypt": "EG", "South Africa": "ZA", "Ecuador": "EC", "Albania": "AL", "Angola": "AO", "Kazakhstan": "KZ", "Ethiopia": "ET", "Zimbabwe": "ZW", "Spain": "ES", "Eritrea": "ER", "Montenegro": "ME", "Moldova, Republic of": "MD", "Madagascar": "MG", "Morocco": "MA", "Uzbekistan": "UZ", "Myanmar": "MM", "Mali": "ML", "Mongolia": "MN", "Macedonia": "MK", "Malawi": "MW", "Mauritania": "MR", "Uganda": "UG", "Malaysia": "MY", "Mexico": "MX", "Vanuatu": "VU", "France": "FR", "Finland": "FI", "Fiji": "FJ", "Falkland Islands (Malvinas)": "FK", "Nicaragua": "NI", "Netherlands": "NL", "Norway": "NO", "Namibia": "NA", "New Caledonia": "NC", "Niger": "NE", "Nigeria": "NG", "New Zealand": "NZ", "Nepal": "NP", "Cote d'Ivoire": "CI", "Switzerland": "CH", "Colombia": "CO", "China": "CN", "Cameroon": "CM", "Chile": "CL", "Canada": "CA", "Congo": "CG", "Central African Republic": "CF", "Congo, The Democratic Republic of the": "CD", "Czech Republic": "CZ", "Cyprus": "CY", "Costa Rica": "CR", "Cuba": "CU", "Swaziland": "SZ", "Syrian Arab Republic": "SY", "Kyrgyzstan": "KG", "Kenya": "KE", "South Sudan": "SS", "Suriname": "SR", "Cambodia": "KH", "El Salvador": "SV", "Slovakia": "SK", "Korea, Republic of": "KR", "Slovenia": "SI", "Korea, Democratic People's Republic of": "KP", "Somalia": "SO", "Senegal": "SN", "Sierra Leone": "SL", "Solomon Islands": "SB", "Saudi Arabia": "SA", "Sweden": "SE", "Sudan": "SD", "Dominican Republic": "DO", "Djibouti": "DJ", "Denmark": "DK", "Germany": "DE", "Yemen": "YE", "Austria": "AT", "Algeria": "DZ", "United States": "US", "Latvia": "LV", "Uruguay": "UY", "Lebanon": "LB", "Lao People's Democratic Republic": "LA", "Taiwan": "TW", "Trinidad and Tobago": "TT", "Turkey": "TR", "Sri Lanka": "LK", "Tunisia": "TN", "Timor-Leste": "TL", "Turkmenistan": "TM", "Tajikistan": "TJ", "Lesotho": "LS", "Thailand": "TH", "French Southern and Antarctic Lands": "TF", "Togo": "TG", "Chad": "TD", "Libyan Arab Jamahiriya": "LY", "United Arab Emirates": "AE", "Venezuela": "VE", "Afghanistan": "AF", "Iraq": "IQ", "Iceland": "IS", "Iran, Islamic Republic of": "IR", "Armenia": "AM", "Italy": "IT", "Vietnam": "VN", "Argentina": "AR", "Australia": "AU", "Israel": "IL", "India": "IN", "Tanzania, United Republic of": "TZ", "Azerbaijan": "AZ", "Ireland": "IE", "Indonesia": "ID", "Ukraine": "UA", "Qatar": "QA", "Mozambique": "MZ"
	};

/* all country names that might be received from IPV, even those without valid 
   geo data.  When in doubt this list must come from IPV and takes precedence
   over name formats embedded in the jquery jvectormap
 */
var all_country_names = [
	"Afghanistan",
	"Aland Islands",
	"Albania",
	"Algeria",
	"American Samoa",
	"Andorra",
	"Angola",
	"Anguilla",
	"Anonymous Proxy",
	"Antarctica",
	"Antigua and Barbuda",
	"Argentina",
	"Armenia",
	"Aruba",
	"Asia/Pacific Region",
	"Australia",
	"Austria",
	"Azerbaijan",
	"Bahamas",
	"Bahrain",
	"Bangladesh",
	"Barbados",
	"Belarus",
	"Belgium",
	"Belize",
	"Benin",
	"Bermuda",
	"Bhutan",
	"Bolivia",
	"Bosnia and Herzegovina",
	"Botswana",
	"Bouvet Island",
	"Brazil",
	"British Indian Ocean Territory",
	"Brunei Darussalam",
	"Bulgaria",
	"Burkina Faso",
	"Burundi",
	"Cambodia",
	"Cameroon",
	"Canada",
	"Cape Verde",
	"Cayman Islands",
	"Central African Republic",
	"Chad",
	"Chile",
	"China",
	"Christmas Island",
	"Cocos (Keeling) Islands",
	"Colombia",
	"Comoros",
	"Congo",
	"Congo, The Democratic Republic of the",
	"Cook Islands",
	"Costa Rica",
	"Cote d'Ivoire",
	"Croatia",
	"Cuba",
	"Cyprus",
	"Czech Republic",
	"Denmark",
	"Djibouti",
	"Dominica",
	"Dominican Republic",
	"Ecuador",
	"Egypt",
	"El Salvador",
	"Equatorial Guinea",
	"Eritrea",
	"Estonia",
	"Ethiopia",
	"Europe",
	"Falkland Islands (Malvinas)",
	"Faroe Islands",
	"Fiji",
	"Finland",
	"France",
	"French Guiana",
	"French Polynesia",
	"French Southern Territories",
	"Gabon",
	"Gambia",
	"Georgia",
	"Germany",
	"Ghana",
	"Gibraltar",
	"Greece",
	"Greenland",
	"Grenada",
	"Guadeloupe",
	"Guam",
	"Guatemala",
	"Guernsey",
	"Guinea",
	"Guinea-Bissau",
	"Guyana",
	"Haiti",
	"Heard Island and McDonald Islands",
	"Holy See (Vatican City State)",
	"Honduras",
	"Hong Kong",
	"Hungary",
	"Iceland",
	"India",
	"Indonesia",
	"Iran, Islamic Republic of",
	"Iraq",
	"Ireland",
	"Isle of Man",
	"Israel",
	"Italy",
	"Jamaica",
	"Japan",
	"Jersey",
	"Jordan",
	"Kazakhstan",
	"Kenya",
	"Kiribati",
	"Korea, Democratic People's Republic of",
	"Korea, Republic of",
	"Kuwait",
	"Kyrgyzstan",
	"Lao People's Democratic Republic",
	"Latvia",
	"Lebanon",
	"Lesotho",
	"Liberia",
	"Libyan Arab Jamahiriya",
	"Liechtenstein",
	"Lithuania",
	"Luxembourg",
	"Macao",
	"Macedonia",
	"Madagascar",
	"Malawi",
	"Malaysia",
	"Maldives",
	"Mali",
	"Malta",
	"Marshall Islands",
	"Martinique",
	"Mauritania",
	"Mauritius",
	"Mayotte",
	"Mexico",
	"Micronesia, Federated States of",
	"Moldova, Republic of",
	"Monaco",
	"Mongolia",
	"Montenegro",
	"Montserrat",
	"Morocco",
	"Mozambique",
	"Myanmar",
	"Namibia",
	"Nauru",
	"Nepal",
	"Netherlands",
	"Netherlands Antilles",
	"New Caledonia",
	"New Zealand",
	"Nicaragua",
	"Niger",
	"Nigeria",
	"Niue",
	"Norfolk Island",
	"Northern Mariana Islands",
	"Norway",
	"Oman",
	"Other Country",
	"Pakistan",
	"Palau",
	"Palestinian Territory",
	"Panama",
	"Papua New Guinea",
	"Paraguay",
	"Peru",
	"Philippines",
	"Pitcairn",
	"Poland",
	"Portugal",
	"Puerto Rico",
	"Qatar",
	"Reunion",
	"Romania",
	"Russian Federation",
	"Rwanda",
	"Saint Helena",
	"Saint Kitts and Nevis",
	"Saint Lucia",
	"Saint Pierre and Miquelon",
	"Saint Vincent and the Grenadines",
	"Samoa",
	"San Marino",
	"Sao Tome and Principe",
	"Satellite Provider",
	"Saudi Arabia",
	"Senegal",
	"Serbia",
	"Seychelles",
	"Sierra Leone",
	"Singapore",
	"Slovakia",
	"Slovenia",
	"Solomon Islands",
	"Somalia",
	"South Africa",
	"South Georgia and the South Sandwich Islands",
	"Spain",
	"Sri Lanka",
	"Sudan",
	"Suriname",
	"Svalbard and Jan Mayen",
	"Swaziland",
	"Sweden",
	"Switzerland",
	"Syrian Arab Republic",
	"Taiwan",
	"Tajikistan",
	"Tanzania, United Republic of",
	"Thailand",
	"Timor-Leste",
	"Togo",
	"Tokelau",
	"Tonga",
	"Trinidad and Tobago",
	"Tunisia",
	"Turkey",
	"Turkmenistan",
	"Turks and Caicos Islands",
	"Tuvalu",
	"Uganda",
	"Ukraine",
	"United Arab Emirates",
	"United Kingdom",
	"United States",
	"United States Minor Outlying Islands",
	"Uruguay",
	"Uzbekistan",
	"Vanuatu",
	"Venezuela",
	"Vietnam",
	"Virgin Islands, British",
	"Virgin Islands, U.S.",
	"Wallis and Futuna",
	"Western Sahara",
	"Yemen",
	"Zambia",
	"Zimbabwe"
];

function ipv_countries_by_code ( code ) {
 	return countries[code.toLowerCase()];
}

function ipv_codes_by_country( country ) {
	code = codes[country];

	if ( typeof code !== 'undefined' ) 
		return code.toUpperCase();
	else 
		return code;
}

// Create an array with the given color for evey country code
// Useful if caller wants to "replace" the colors on a jvectormap
// since passing it a new array of colors just modifies the overlapping
// countries but does not reset missing countries to default

function ipv_clear_colors( color_spec ) {

	color = {};

	for ( code in countries ) {
		color[code.toUpperCase()] = color_spec;
	}

	return color;

}

// return an array of all the country names
function ipv_country_names () {

	names = new Array();

	for ( name in codes ) {
		names.push( name );
	}
	return names;
	
}
