import { apiRequest } from './ApiClient.js';

export const LibraryModel = {
  listBooks(page = 1) {
    return apiRequest(`/books?page=${page}`);
  },
  createBook(data) {
    return apiRequest('/books', { method: 'POST', body: JSON.stringify(data) });
  },
  listLoans() {
    return apiRequest('/book-loans');
  },
  issue(data) {
    return apiRequest('/book-loans/issue', { method: 'POST', body: JSON.stringify(data) });
  },
  returnLoan(loanId) {
    return apiRequest(`/book-loans/${loanId}/return`, { method: 'POST', body: JSON.stringify({}) });
  },
};
