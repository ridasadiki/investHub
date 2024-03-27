import {useEffect, useState} from "react";
import axiosClient from "../axios-client.js";
import {Link} from "react-router-dom";
import {useStateContext} from "../context/ContextProvider.jsx";

export default function Products() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const {setNotification} = useStateContext()

  useEffect(() => {
    getProducts();
  }, [])

  const onDeleteClick = product => {
    if (!window.confirm("Are you sure you want to delete this product?")) {
      return
    }
    axiosClient.delete(`/products/${product.id}`)
      .then(() => {
        setNotification('Product was successfully deleted')
        getProducts()
      })
  }

  const getProducts = () => {
    setLoading(true)
    axiosClient.get('/products')
      .then(({ data }) => {
        setLoading(false)
        setProducts(data.data)
      })
      .catch(() => {
        setLoading(false)
      })
  }

  return (
    <div>
      <div style={{display: 'flex', justifyContent: "space-between", alignItems: "center"}}>
        <h1>Products</h1>
        <Link className="btn-add" to="/products/new">Add new</Link>
      </div>
      <div className="card animated fadeInDown">
        <table>
          <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Unit</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Phone Number</th>
            <th>Actions</th>
          </tr>
          </thead>
          {loading &&
            <tbody>
            <tr>
              <td colSpan="5" className="text-center">
                Loading...
              </td>
            </tr>
            </tbody>
          }
          {!loading &&
            <tbody>
            {products.map((u, index) => (
                <tr key={u.id}>
                <td>{index + 1}</td>
                <td>{u.name}</td>
                <td>{u.unit}</td>
                <td>{u.price}</td>
                <td>{u.quantity}</td>
                <td className={u.status === 'Pending' ? 'red-status' : ''}>{u.status}</td>
                <td>{u.phone_number}</td>
                <td>
                    <Link className="btn-edit" to={'/products/' + u.id}>Edit</Link>
                    &nbsp;
                    <button className="btn-delete" onClick={ev => onDeleteClick(u)}>Delete</button>
                </td>
                </tr>
            ))}
            </tbody>
          }
        </table>
      </div>
    </div>
  )
}
