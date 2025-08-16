describe('Smoke - homepage', () => {
  it('loads', () => {
    cy.visit('/');
    cy.contains('Archmage').should('exist');
  });
});
