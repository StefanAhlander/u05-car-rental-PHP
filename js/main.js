const elementIdSet = [
  {
    id: 'validate',
    event: 'submit',
    handler: validate
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
      returnMessage += `Person number should start with a valid date in the format YYYYMMDD.\n\n`;
    }

    let check = [...num.slice(2)]
      .map((n, index) => (index % 2 ? 1 : 2) * n)
      .join('')
      .split('')
      .reduce((acc, cur) => acc + +cur, 0);

    if (check % 10 !== 0) {
      returnMessage += `The control number for person nummer is incorrect.\n\n`;
    }

    return returnMessage;
  },
  phonenumber(elt) {
    let num = elt.value.replace(/[-\s]/g, '');
    if (!/^0\d{9}$/.test(num)) {
      return `Phonenumber should be 10 figures, start with 0 and only contain numbers.\n\n`;
    }
    return '';
  }
};

function validate(e) {
  const list = document.querySelectorAll('[data-validate]');
  let message = 'blocked \n';

  list.forEach(elt => {
    message += validateMethods[elt.dataset.validate](elt);
  });

  if (message) {
    e.preventDefault();
    alert('Your form contains errors!\n\n' + message);
    return false;
  }
}

elementIdSet.forEach(item => {
  document.getElementById(item.id) &&
    document.getElementById(item.id).addEventListener(item.event, item.handler);
});
