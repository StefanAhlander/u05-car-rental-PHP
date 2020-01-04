const elementIdSet = [
  {
    id: 'validate',
    event: 'submit',
    handler: validate
  },
  {
    id: 'selectMake',
    event: 'change',
    handler: removeChild
  },
  {
    id: 'selectColor',
    event: 'change',
    handler: removeChild
  }
];

const validateMethods = {
  personnumber(elt) {
    let returnMessage = '';

    let num = elt.value.replace(/[-\s]/g, '');
    if (!/^\d{12}$/.test(num)) {
      return `Person number should be 12 figures and only numbers.\n`;
    }

    let time = new Date(
      ('' + num)
        .match(/^(\d{4})(\d{2})(\d{2})/)
        .slice(1)
        .join('-')
    ).getTime();

    if (isNaN(time)) {
      returnMessage += `Person number should start with a valid date in the format YYYYMMDD.\n`;
    }

    let check = [...num.slice(2)]
      .map((n, index) => (index % 2 ? 1 : 2) * n)
      .join('')
      .split('')
      .reduce((acc, cur) => acc + +cur, 0);

    if (check % 10 !== 0) {
      returnMessage += `The control number for person nummer is incorrect.\n`;
    }
    return returnMessage;
  },
  phonenumber(elt) {
    let returnMessage = '';

    let num = elt.value.replace(/[-\s]/g, '');
    if (!/^0\d{9}$/.test(num)) {
      returnMessage += `Phonenumber should be 10 figures, start with 0 and only contain numbers.\n`;
    }
    return returnMessage;
  },
  registration(elt) {
    let returnMessage = '';
    elt.value = elt.value.replace(/[a-z]/gi, l => l.toUpperCase());

    let num = elt.value.replace(/[-\s]/g, '');
    if (!/^[a-hj-pr-uw-z]{3}\d{2}[a-hj-npr-uw-z0-9]{1}$/i.test(num)) {
      returnMessage += `The registration doesn\'t comply with Swedish standards.\n`;
    }
    return returnMessage;
  },
  year(elt) {
    let returnMessage = '';

    let num = elt.value.replace(/[-\s]/g, '');
    if (!/^\d{4}$/.test(num) || num < 1900 || num > 2020) {
      returnMessage += `The registration year should be a whole number between 1900 and 2020.\n`;
    }
    return returnMessage;
  },
  price(elt) {
    let returnMessage = '';

    let num = elt.value.replace(',', '.');
    if (!/^[\d.]+$/.test(num)) {
      returnMessage += `The the price should only contain numbers and an optional separator.\n`;
    }

    num = Number.parseFloat(num).toFixed(2);
    if (isNaN(num) || num < 0) {
      returnMessage += `The price should be a positive number.\n`;
    }
    return returnMessage;
  }
};

function validate(e) {
  const list = document.querySelectorAll('[data-validate]');
  let message = '';

  list.forEach(elt => {
    message += validateMethods[elt.dataset.validate](elt);
  });

  if (message) {
    e.preventDefault();
    alert('Your form contains errors!\n' + message);
    return false;
  }
}

function removeChild(elt) {
  document.querySelector('.remove').remove();
}

elementIdSet.forEach(item => {
  document.getElementById(item.id) &&
    document.getElementById(item.id).addEventListener(item.event, item.handler);
});
