import {useNavigate, useParams} from "react-router-dom";
import {useEffect, useState} from "react";
import axiosClient from "../axios-client.js";
import {useStateContext} from "../context/ContextProvider.jsx";

export default function ProductForm() {
  const navigate = useNavigate();
  let {id} = useParams();
  const [product, setProduct] = useState({
    id: null,
    name: '',
    unit: '',
    price: '',
    quantity: '',
    phone_number: '',
    status: '',
  })
  const [errors, setErrors] = useState(null)
  const [loading, setLoading] = useState(false)
  const {setNotification} = useStateContext()

  if (id) {
    useEffect(() => {
      setLoading(true)
      axiosClient.get(`/products/${id}`)
        .then(({data}) => {
          setLoading(false)
          setProduct(data)
        })
        .catch(() => {
          setLoading(false)
        })
    }, [])
  }

  const onSubmit = ev => {
    ev.preventDefault()
    if (product.id) {
      axiosClient.put(`/products/${product.id}`, product)
        .then(() => {
          setNotification('Product was successfully updated')
          navigate('/products')
        })
        .catch(err => {
          const response = err.response;
          if (response && response.status === 422) {
            setErrors(response.data.errors)
          }
        })
    } else {
      axiosClient.post('/products', product)
        .then(() => {
          setNotification('Product was successfully created')
          navigate('/products')
        })
        .catch(err => {
          const response = err.response;
          if (response && response.status === 422) {
            setErrors(response.data.errors)
          }
        })
    }
  }

  return (
    <>
      {product.id && <h1>Update Product: {product.name}</h1>}
      {product.id && <div>Phone Number: {product.phone_number}</div>}
      {!product.id && <h1>New Product</h1>}
      <div className="card animated fadeInDown">
        {loading && (
          <div className="text-center">
            Loading...
          </div>
        )}
        {errors &&
          <div className="alert">
            {Object.keys(errors).map(key => (
              <p key={key}>{errors[key][0]}</p>
            ))}
          </div>
        }
        {!loading && (
          <form onSubmit={onSubmit}>
              <input value={product.name} onChange={ev => setProduct({...product, name: ev.target.value})} placeholder="Name"/>
              <input value={product.unit} onChange={ev => setProduct({...product, unit: ev.target.value})} placeholder="Unit"/>
              <input value={product.quantity} onChange={ev => setProduct({...product, quantity: ev.target.value})} placeholder="quantity"/>
              <input value={product.price} onChange={ev => setProduct({...product, price: ev.target.value})} placeholder="price"/>
              <input value={product.phone_number} onChange={ev => setProduct({...product, phone_number: ev.target.value})} placeholder="Phone Number"/>
              <select  value={product.status} onChange={(ev) => setProduct({ ...product, status: ev.target.value }) }>
                <option value="">--Select Status--</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
              </select>
            <button className="btn">Save</button>
          </form>
        )}
      </div>
    </>
  )
}
